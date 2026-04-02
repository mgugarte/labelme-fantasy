# update_market_values.py
# Obtiene valores de mercado de apps Fantasy y los guarda en MySQL
# Apps: Mister | Biwenger | Comunio | LaLiga Fantasy

import requests
import pymysql
import os
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

def get_player_id_by_name(name):
    name = name.strip()
    cursor.execute(
        "SELECT id FROM jugadores_laliga WHERE nombre = %s AND season = '2025' LIMIT 1",
        (name,)
    )
    row = cursor.fetchone()
    if row:
        return row[0]
    parts = name.split()
    if parts:
        apellido = parts[-1]
        cursor.execute(
            "SELECT id FROM jugadores_laliga WHERE nombre LIKE %s AND season = '2025' LIMIT 1",
            (f'%{apellido}%',)
        )
        row = cursor.fetchone()
        if row:
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

def login_mister():
    import base64, json as _json
    session = requests.Session()
    headers = {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        'accept': 'application/json',
        'content-type': 'application/json',
        'origin': 'https://mister.mundodeportivo.com',
        'referer': 'https://mister.mundodeportivo.com/',
    }
    state = requests.utils.quote(base64.b64encode(_json.dumps(
        {"method": "email", "platform": "web", "userAgent": "Mozilla/5.0"},
        separators=(',', ':')
    ).encode()).decode())
    try:
        r = session.post(
            f'https://mister.mundodeportivo.com/api2/auth/email?state={state}',
            json={'email': os.getenv('MISTER_EMAIL'), 'password': os.getenv('MISTER_PASSWORD')},
            headers=headers, timeout=30
        )
        token = r.json().get('token')
        if token:
            print("  Login Mister OK")
            return token
        print(f"  Login Mister fallido: {r.text[:200]}")
        return None
    except Exception as e:
        print(f"  ERROR login Mister: {e}")
        return None

def fetch_mister():
    print("=== MISTER ===")
    token = login_mister()
    if not token:
        print("  Sin token válido — omitiendo Mister")
        return
    headers = {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        'accept': 'application/json',
        'Authorization': f'Bearer {token}',
        'origin': 'https://mister.mundodeportivo.com',
        'referer': 'https://mister.mundodeportivo.com/',
    }
    total_saved = 0
    not_found = []
    try:
        r = requests.get(
            'https://mister.mundodeportivo.com/api2/competitions/1/players?limit=600',
            headers=headers, timeout=30
        )
        data = r.json()
    except Exception as e:
        print(f"  ERROR: {e}")
        return
    players = data.get('items', [])
    print(f"  {len(players)} jugadores recibidos")
    for p in players:
        name = p.get('name', '')
        value = p.get('value', 0)
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
        data = r.json()
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

    # 2. Scraping de cada perfil para obtener el valor de mercado
    total_saved = 0
    not_found = []
    errors = 0
    for p in players:
        pid = p['id']
        name = p['value'].strip()
        try:
            r2 = requests.get(f'https://stats.comunio.es/csprofile/{pid}-', headers=headers, timeout=15)
            html = r2.content.decode('utf-8', errors='replace')
            m2 = _re.search(r'"playerData":\{"playerId":\d+,"name":"[^"]+","price":(\d+)', html)
            if not m2:
                not_found.append(name)
                continue
            value = int(m2.group(1))
            if value <= 0:
                not_found.append(name)
                continue
            player_id = get_player_id_by_name(name)
            if player_id:
                save_value(player_id, 'comunio', value)
                total_saved += 1
            else:
                not_found.append(name)
            _time.sleep(0.3)
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
        for item in r.json():
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
        teams = r.json()
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

        print(f"  Total jugadores únicos: {len(players_found)}")

    except Exception as e:
        print(f"  ERROR equipos: {e}")

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