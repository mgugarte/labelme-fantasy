# update_market_values.py
# Obtiene valores de mercado de apps Fantasy y los guarda en MySQL
# Apps: Mister | Biwenger | Comunio | LaLiga Fantasy

import requests
import pymysql
import os
import unicodedata
import json as _json_module
from datetime import date
from dotenv import load_dotenv

load_dotenv(os.path.join(os.path.dirname(__file__), '.env'))

# === CONEXIÓN BBDD ===
conn = pymysql.connect(
    host=os.getenv('DB_HOST'),
    user=os.getenv('DB_USER'),
    password=os.getenv('DB_PASSWORD'),
    database=os.getenv('DB_NAME'),
    charset='utf8mb4'
)
cursor = conn.cursor()
cursor.execute("SET SESSION innodb_lock_wait_timeout = 60")

TODAY = date.today()

# ============================================================
# UTILIDADES
# ============================================================

def _normalize(s):
    """Lowercase + strip accents for fuzzy matching."""
    return ''.join(
        c for c in unicodedata.normalize('NFKD', s.lower().strip())
        if not unicodedata.combining(c)
    )

def get_player_id_by_name(name):
    name = name.strip()
    if not name:
        return None

    # 1. Exact match
    cursor.execute(
        "SELECT id FROM jugadores_laliga WHERE nombre = %s AND season = '2025' LIMIT 1",
        (name,)
    )
    row = cursor.fetchone()
    if row:
        return row[0]

    # 2. Case-insensitive + accent-insensitive (COLLATE utf8mb4_unicode_ci treats é=e, ñ≈n, etc.)
    cursor.execute(
        "SELECT id FROM jugadores_laliga "
        "WHERE nombre COLLATE utf8mb4_unicode_ci = %s AND season = '2025' LIMIT 1",
        (name,)
    )
    row = cursor.fetchone()
    if row:
        return row[0]

    # 3. Apellido (last word) with collation
    parts = name.split()
    if parts:
        apellido = parts[-1]
        cursor.execute(
            "SELECT id FROM jugadores_laliga "
            "WHERE nombre LIKE %s COLLATE utf8mb4_unicode_ci AND season = '2025' LIMIT 1",
            (f'%{apellido}%',)
        )
        row = cursor.fetchone()
        if row:
            return row[0]

    # 4. Python-side normalization: strip accents/diacritics from both sides
    name_norm = _normalize(name)
    cursor.execute(
        "SELECT id, nombre FROM jugadores_laliga WHERE season = '2025' "
        "AND LOWER(nombre) LIKE %s LIMIT 20",
        (f'%{name_norm.split()[-1] if name_norm.split() else name_norm}%',)
    )
    for row in cursor.fetchall():
        if _normalize(row[1]) == name_norm:
            return row[0]

    return None

def save_value(player_id, app, value):
    cursor.execute(
        """SELECT value FROM fantasy_market_values
           WHERE player_id=%s AND app=%s AND recorded_at < %s
           ORDER BY recorded_at DESC LIMIT 1""",
        (player_id, app, TODAY)
    )
    prev = cursor.fetchone()
    change = value - prev[0] if prev else 0
    cursor.execute("""
        INSERT INTO fantasy_market_values 
            (player_id, app, value, value_change, recorded_at)
        VALUES (%s, %s, %s, %s, %s)
        ON DUPLICATE KEY UPDATE 
            value = VALUES(value),
            value_change = VALUES(value_change)
    """, (player_id, app, value, change, TODAY))
    conn.commit()

# ============================================================
# MISTER FANTASY
# ============================================================

def _mister_session():
    """Abre sesión en Mister: PHPSESSID anónimo + login JWT + exchange-token.
    Devuelve una requests.Session lista para usar /ajax/sw/ o None si falla."""
    import base64, json as _json
    session = requests.Session()
    session.headers.update({
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        'accept': '*/*',
        'origin': 'https://mister.mundodeportivo.com',
        'referer': 'https://mister.mundodeportivo.com/search',
        'x-auth': os.getenv('MISTER_XAUTH'),
        'x-requested-with': 'XMLHttpRequest',
    })
    try:
        # Paso 1: obtener PHPSESSID anónimo
        session.get('https://mister.mundodeportivo.com', timeout=10)

        # Paso 2: login → JWT
        state = requests.utils.quote(base64.b64encode(_json.dumps(
            {"method": "email", "platform": "web", "userAgent": "Mozilla/5.0"},
            separators=(',', ':')
        ).encode()).decode())
        session.headers['content-type'] = 'application/json'
        r = session.post(
            f'https://mister.mundodeportivo.com/api2/auth/email?state={state}',
            json={'email': os.getenv('MISTER_EMAIL'), 'password': os.getenv('MISTER_PASSWORD')},
            timeout=30
        )
        token = r.json().get('token')
        if not token:
            print(f"  Login Mister fallido: {r.text[:200]}")
            return None

        # Paso 3: exchange-token → cookie token (JWT corto) + PHPSESSID autenticado
        session.headers['Authorization'] = f'Bearer {token}'
        session.post(
            'https://mister.mundodeportivo.com/api2/auth/external/exchange-token',
            json={'token': token}, timeout=15
        )
        del session.headers['Authorization']
        session.headers['content-type'] = 'application/x-www-form-urlencoded; charset=UTF-8'
        print("  Login Mister OK")
        return session
    except Exception as e:
        print(f"  ERROR login Mister: {e}")
        return None

def fetch_mister():
    """Obtiene valores de mercado de Mister via /ajax/sw/players (POST form).
    Busca por prefijo, expandiendo a dos letras cuando se alcanza el límite de 50."""
    import time as _time
    LIMIT = 50
    LETTERS = 'abcdefghijklmnopqrstuvwxyz'

    print("=== MISTER ===")
    session = _mister_session()
    if not session:
        print("  Sin sesión válida — omitiendo Mister")
        return

    def search(prefix):
        """Devuelve lista de jugadores para el prefijo dado."""
        try:
            r = session.post(
                'https://mister.mundodeportivo.com/ajax/sw/players',
                data={'competition_id': '1', 'name': prefix},
                timeout=20
            )
            _time.sleep(0.05)
            return r.json().get('data', {}).get('players', [])
        except Exception as e:
            print(f"  ERROR buscando '{prefix}': {e}")
            return []

    # Acumular jugadores únicos con expansión automática cuando se alcanza el límite
    all_players = {}
    for letra in LETTERS:
        players = search(letra)
        if len(players) < LIMIT:
            for p in players:
                all_players[p['id']] = p
        else:
            # Límite alcanzado: expandir con segunda letra para no perder jugadores
            for p in players:
                all_players[p['id']] = p
            for letra2 in LETTERS:
                players2 = search(letra + letra2)
                for p in players2:
                    all_players[p['id']] = p
                # Si aún se alcanza el límite con 2 letras, expandir a 3
                if len(players2) >= LIMIT:
                    for letra3 in LETTERS:
                        players3 = search(letra + letra2 + letra3)
                        for p in players3:
                            all_players[p['id']] = p

    print(f"  {len(all_players)} jugadores únicos recibidos")

    total_saved = 0
    not_found = []
    for p in all_players.values():
        name = p.get('name', '')
        value = p.get('value') or 0
        if not name or not value:
            continue
        player_id = get_player_id_by_name(name)
        if player_id:
            save_value(player_id, 'mister', value)
            total_saved += 1
        else:
            not_found.append(name)

    print(f"  TOTAL guardados: {total_saved}")
    if not_found:
        print(f"  No encontrados ({len(not_found)}): {not_found[:10]}...")

# ============================================================
# BIWENGER
# ============================================================

BIWENGER_HEADERS = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
    'Referer': 'https://biwenger.as.com/',
    'Accept': 'application/json, text/plain, */*',
    'Accept-Language': 'es-ES,es;q=0.9',
    'Origin': 'https://biwenger.as.com',
}

def fetch_biwenger():
    print("=== BIWENGER ===")
    try:
        r = requests.get(
            "https://cf.biwenger.com/api/v2/competitions/la-liga/data?lang=es&score=1",
            headers=BIWENGER_HEADERS, timeout=30
        )
        try:
            data = _json_module.loads(r.content.decode('utf-8'))
        except (UnicodeDecodeError, ValueError):
            data = _json_module.loads(r.content.decode('latin-1'))
    except Exception as e:
        print(f"  ERROR al obtener datos: {e}")
        return
    players = data.get('data', {}).get('players', {})
    total_saved = 0
    not_found = []
    for _, player in players.items():
        name = player.get('name', '')
        value = player.get('price', 0)
        if not name or not value:
            continue
        player_id = get_player_id_by_name(name)
        if player_id:
            save_value(player_id, 'biwenger', value)
            total_saved += 1
        else:
            not_found.append(name)
    print(f"  TOTAL guardados: {total_saved}")
    if not_found:
        print(f"  No encontrados ({len(not_found)}): {not_found[:10]}...")

# ============================================================
# COMUNIO — scraping público de stats.comunio.es
# ============================================================

def fetch_comunio():
    import json as _json
    import time as _time
    print("=== COMUNIO ===")
    headers = {'User-Agent': 'Mozilla/5.0', 'Accept-Language': 'es-ES'}

    # 1. Obtener lista de todos los jugadores
    try:
        r = requests.get('https://stats.comunio.es/data/playerSearch.js', headers=headers, timeout=15)
        import re as _re
        m = _re.search(r'searchPlayers=(\[.*\])', r.content.decode('utf-8', errors='replace'))
        if not m:
            print("  ERROR: no se encontró la lista de jugadores")
            return
        players = _json.loads(m.group(1))
        print(f"  {len(players)} jugadores en lista")
    except Exception as e:
        print(f"  ERROR obteniendo lista: {e}")
        return

    # 2. API directa de Comunio (sin autenticación, devuelve price por ID)
    api_headers = {
        'User-Agent': 'Mozilla/5.0',
        'Accept': 'application/json',
        'Accept-Language': 'es-ES',
    }
    total_saved = 0
    not_found = []
    errors = 0
    for p in players:
        pid = p['id']
        name = p['value'].strip()
        try:
            r2 = requests.get(f'https://api.comunio.es/players/{pid}', headers=api_headers, timeout=15)
            if r2.status_code != 200:
                not_found.append(name)
                continue
            try:
                d = _json_module.loads(r2.content.decode('utf-8'))
            except (UnicodeDecodeError, ValueError):
                d = _json_module.loads(r2.content.decode('latin-1'))
            value = d.get('price') or 0
            if not value or value <= 0:
                not_found.append(name)
                continue
            player_id = get_player_id_by_name(name)
            if player_id:
                save_value(player_id, 'comunio', value)
                total_saved += 1
            else:
                not_found.append(name)
            _time.sleep(0.1)
        except Exception as e:
            errors += 1
            if errors <= 3:
                print(f"  ERROR {name}: {e}")

    print(f"  TOTAL guardados: {total_saved}")
    if not_found:
        print(f"  No encontrados ({len(not_found)}): {not_found[:10]}...")

# ============================================================
# LALIGA FANTASY
# ============================================================

def login_laliga_fantasy():
    try:
        r = requests.post(
            "https://login.laliga.es/laligadspprob2c.onmicrosoft.com/oauth2/v2.0/token?p=B2C_1A_ResourceOwnerv2",
            data={
                "grant_type": "password",
                "client_id": "af88bcff-1157-40a0-b579-030728aacf0b",
                "scope": "openid af88bcff-1157-40a0-b579-030728aacf0b offline_access",
                "redirect_uri": "authredirect://com.lfp.laligafantasy",
                "username": os.getenv('LALIGA_EMAIL'),
                "password": os.getenv('LALIGA_PASSWORD'),
                "response_type": "id_token"
            },
            timeout=30
        )
        if r.status_code == 200:
            print("  Login LaLiga Fantasy OK")
            return r.json().get("id_token")
        print(f"  Login LaLiga Fantasy fallido: {r.status_code} {r.text[:200]}")
        return None
    except Exception as e:
        print(f"  ERROR login LaLiga Fantasy: {e}")
        return None

def upsert_laliga_player(laliga_id, nickname, name, team_id, position_id, market_value):
    cursor.execute(
        "SELECT player_id FROM laliga_fantasy_player_ids WHERE laliga_fantasy_id = %s",
        (laliga_id,)
    )
    row = cursor.fetchone()
    player_id = row[0] if row else None
    if not player_id:
        player_id = get_player_id_by_name(nickname) or get_player_id_by_name(name)
    cursor.execute("""
        INSERT INTO laliga_fantasy_player_ids
            (laliga_fantasy_id, player_id, nickname, name, team_id, position_id, last_market_value, updated_at)
        VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
        ON DUPLICATE KEY UPDATE
            player_id = COALESCE(VALUES(player_id), player_id),
            nickname = VALUES(nickname),
            name = VALUES(name),
            team_id = VALUES(team_id),
            position_id = VALUES(position_id),
            last_market_value = VALUES(last_market_value),
            updated_at = VALUES(updated_at)
    """, (laliga_id, player_id, nickname, name, team_id, position_id, market_value, TODAY))
    conn.commit()
    return player_id

def fetch_laliga_fantasy():
    print("=== LALIGA FANTASY ===")

    token = login_laliga_fantasy()
    if not token:
        print("  Sin token válido — omitiendo LaLiga Fantasy")
        return

    league_id = os.getenv('LALIGA_LEAGUE_ID', '1098589')
    headers = {
        "Authorization": f"Bearer {token}",
        "X-App": "Fantasy-web",
        "X-Lang": "es",
        "Origin": "https://laligafantasy.relevo.com",
        "Referer": "https://laligafantasy.relevo.com/"
    }

    players_found = {}

    def extract_player(pm, market_value=None):
        if not isinstance(pm, dict):
            return
        pid = str(pm.get('id', ''))
        if not pid:
            return
        value = market_value or pm.get('marketValue', 0)
        players_found[pid] = {
            'laliga_fantasy_id': pid,
            'nickname': pm.get('nickname', ''),
            'name': pm.get('name', ''),
            'team_id': str(pm.get('teamId', '')),
            'position_id': str(pm.get('positionId', '')),
            'market_value': value,
        }

    # Fuente 1: Mercado de la liga
    try:
        r = requests.get(
            f"https://api-fantasy.llt-services.com/api/v3/league/{league_id}/market?x-lang=es",
            headers=headers, timeout=30
        )
        for item in _json_module.loads(r.content.decode('utf-8')):
            pm = item.get('playerMaster', {})
            mv = item.get('marketValue') or pm.get('marketValue', 0)
            extract_player(pm, mv)
        print(f"  Mercado: {len(players_found)} jugadores")
    except Exception as e:
        print(f"  ERROR mercado: {e}")

    # Fuente 2: Todos los equipos de la liga (incluye jugadores con marketValue)
    try:
        r = requests.get(
            f"https://api-fantasy.llt-services.com/api/v4/leagues/{league_id}/teams?x-lang=es",
            headers=headers, timeout=30
        )
        teams = _json_module.loads(r.content.decode('utf-8'))
        if not isinstance(teams, list):
            print(f"  ERROR equipos inesperado: {str(teams)[:200]}")
            raise ValueError("Equipos no es una lista")

        print(f"  {len(teams)} equipos en la liga")
        for team in teams:
            for p in team.get('players', []) + team.get('loanedPlayers', []):
                if isinstance(p, dict):
                    pm = p.get('playerMaster', {})
                    mv = pm.get('marketValue', 0)
                    extract_player(pm, mv)

    except Exception as e:
        print(f"  ERROR equipos: {e}")

    # Fuente 3: Iteración de IDs con /api/v3/player/{id}?includeMarketValue=true
    try:
        from concurrent.futures import ThreadPoolExecutor, as_completed as _as_completed
        before = len(players_found)
        session = requests.Session()
        session.headers.update(headers)

        def _fetch_player(pid):
            try:
                r2 = session.get(
                    f"https://api-fantasy.llt-services.com/api/v3/player/{pid}?x-lang=es&includeMarketValue=true",
                    timeout=10
                )
                if r2.status_code == 200:
                    return pid, _json_module.loads(r2.content.decode('utf-8'))
            except Exception:
                pass
            return pid, None

        with ThreadPoolExecutor(max_workers=15) as ex:
            futures = {ex.submit(_fetch_player, pid): pid for pid in range(1, 3001)}
            for f in _as_completed(futures):
                pid, data = f.result()
                if not data:
                    continue
                mv = data.get('marketValue', 0)
                if not mv:
                    continue
                team = data.get('team', {})
                tid = str(team.get('id', '')) if isinstance(team, dict) else ''
                players_found[str(pid)] = {
                    'laliga_fantasy_id': str(pid),
                    'nickname': data.get('nickname', ''),
                    'name': data.get('name', ''),
                    'team_id': tid,
                    'position_id': str(data.get('positionId', '')),
                    'market_value': mv,
                }

        added = len(players_found) - before
        print(f"  Fuente3 (iteracion IDs 1-3000): +{added} jugadores nuevos")
    except Exception as e:
        print(f"  ERROR fuente3: {e}")

    print(f"  Total jugadores únicos: {len(players_found)}")

    # Guardar en BD
    total_saved = 0
    not_found = []
    for pid, p in players_found.items():
        try:
            player_id = upsert_laliga_player(
                pid, p['nickname'], p['name'],
                p['team_id'], p['position_id'], p['market_value']
            )
            if player_id and p['market_value']:
                save_value(player_id, 'laliga_fantasy', p['market_value'])
                total_saved += 1
            elif not player_id:
                not_found.append(p['nickname'] or p['name'])
        except Exception as e:
            conn.rollback()
            print(f"  ERROR guardando {p['nickname']}: {e}")

    print(f"  Valores guardados: {total_saved}")
    if not_found:
        print(f"  Sin mapear todavía ({len(not_found)}): {not_found[:10]}...")

# ============================================================
# EJECUCIÓN
# ============================================================

apps = [
    ('Mister',         fetch_mister),
    ('Biwenger',       fetch_biwenger),
    ('Comunio',        fetch_comunio),
    ('LaLiga Fantasy', fetch_laliga_fantasy),
]

for app_name, fetch_fn in apps:
    try:
        fetch_fn()
    except Exception as e:
        print(f"ERROR {app_name}: {e}")

cursor.close()
conn.close()
print("\nFinalizado.")