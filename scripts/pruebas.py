"""
Prueba: cuántos jugadores se recuperan de Mister con la nueva lógica de prefijos.
Muestra conteo por letra y cuáles letras necesitaron expansión a 2/3 letras.
"""
import sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

import requests, os, time, base64, json
from dotenv import load_dotenv

load_dotenv(os.path.join(os.path.dirname(__file__), '.env'))

LIMIT = 50
LETTERS = 'abcdefghijklmnopqrstuvwxyz'

# ── Auth ────────────────────────────────────────────────────────────────────

def _mister_session():
    session = requests.Session()
    session.headers.update({
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        'accept': '*/*',
        'origin': 'https://mister.mundodeportivo.com',
        'referer': 'https://mister.mundodeportivo.com/search',
        'x-auth': os.getenv('MISTER_XAUTH'),
        'x-requested-with': 'XMLHttpRequest',
    })
    # Paso 1: PHPSESSID anónimo
    session.get('https://mister.mundodeportivo.com', timeout=10)

    # Paso 2: login → JWT
    state = requests.utils.quote(base64.b64encode(json.dumps(
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
        print(f"Login fallido: {r.text[:200]}")
        return None

    # Paso 3: exchange-token
    session.headers['Authorization'] = f'Bearer {token}'
    session.post(
        'https://mister.mundodeportivo.com/api2/auth/external/exchange-token',
        json={'token': token}, timeout=15
    )
    del session.headers['Authorization']
    session.headers['content-type'] = 'application/x-www-form-urlencoded; charset=UTF-8'
    print("Login Mister OK")
    return session

# ── Búsqueda ─────────────────────────────────────────────────────────────────

session = _mister_session()
if not session:
    sys.exit(1)

def search(prefix):
    try:
        r = session.post(
            'https://mister.mundodeportivo.com/ajax/sw/players',
            data={'competition_id': '1', 'name': prefix},
            timeout=20
        )
        time.sleep(0.05)
        return r.json().get('data', {}).get('players', [])
    except Exception as e:
        print(f"  ERROR '{prefix}': {e}")
        return []

# ── Iteración con expansión ──────────────────────────────────────────────────

all_players = {}
total_requests = 0
expanded_letters = []   # letras que necesitaron expansión a 2
expanded_2letters = []  # combinaciones que necesitaron expansión a 3

print("\nBuscando jugadores...\n")
start = time.time()

for letra in LETTERS:
    players = search(letra)
    total_requests += 1
    count_before = len(all_players)

    if len(players) < LIMIT:
        for p in players:
            all_players[p['id']] = p
        print(f"  '{letra}' → {len(players)} jugadores")
    else:
        # Límite alcanzado — expandir a 2 letras
        for p in players:
            all_players[p['id']] = p
        expanded_letters.append(letra)
        sub_counts = {}
        for letra2 in LETTERS:
            prefix2 = letra + letra2
            players2 = search(prefix2)
            total_requests += 1
            for p in players2:
                all_players[p['id']] = p
            sub_counts[prefix2] = len(players2)

            if len(players2) >= LIMIT:
                # Expandir a 3 letras
                expanded_2letters.append(prefix2)
                for letra3 in LETTERS:
                    prefix3 = letra + letra2 + letra3
                    players3 = search(prefix3)
                    total_requests += 1
                    for p in players3:
                        all_players[p['id']] = p

        added = len(all_players) - count_before
        print(f"  '{letra}' → LÍMITE alcanzado, expandido a 2 letras → +{added} jugadores")
        saturated = [k for k, v in sub_counts.items() if v >= LIMIT]
        if saturated:
            print(f"    Combinaciones que también saturaron (→ 3 letras): {saturated}")

elapsed = time.time() - start

# ── Resumen ──────────────────────────────────────────────────────────────────

print(f"\n{'='*50}")
print(f"Total jugadores únicos : {len(all_players)}")
print(f"Total peticiones HTTP  : {total_requests}")
print(f"Tiempo total           : {elapsed:.1f}s")
print(f"Letras expandidas a 2  : {expanded_letters or 'ninguna'}")
print(f"Prefijos expandidos a 3: {expanded_2letters or 'ninguno'}")

# Jugadores sin valor
sin_valor = [p for p in all_players.values() if not p.get('value')]
print(f"\nJugadores con value=0  : {len(sin_valor)}")

# Top 10 por valor
print("\nTop 10 por valor:")
for p in sorted(all_players.values(), key=lambda x: x.get('value', 0), reverse=True)[:10]:
    print(f"  [{p['id']}] {p.get('name', '')} — {p.get('value', 0):,}")
