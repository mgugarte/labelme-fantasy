# laliga_fantasy_ids.py
# Obtiene el mapeo de IDs de LaLiga Fantasy para todos los jugadores disponibles
# Estrategia: mercado de la liga + lineups de todos los equipos

import requests
import json
import time

# ==========================================
EMAIL = "miguelgonzalezugarte@gmail.com"
PASSWORD = "k@qt+qAPUW4?t%5"
LEAGUE_ID = "1098589"  # tu liga
# ==========================================

# ==========================================

# Login
print("Haciendo login...")
r = requests.post(
    "https://login.laliga.es/laligadspprob2c.onmicrosoft.com/oauth2/v2.0/token?p=B2C_1A_ResourceOwnerv2",
    data={
        "grant_type": "password",
        "client_id": "af88bcff-1157-40a0-b579-030728aacf0b",
        "scope": "openid af88bcff-1157-40a0-b579-030728aacf0b offline_access",
        "redirect_uri": "authredirect://com.lfp.laligafantasy",
        "username": EMAIL,
        "password": PASSWORD,
        "response_type": "id_token"
    }
)
id_token = r.json()["id_token"]
print("✅ Login OK")

headers = {
    "Authorization": f"Bearer {id_token}",
    "X-App": "Fantasy-web",
    "X-Lang": "es",
    "Origin": "https://laligafantasy.relevo.com",
    "Referer": "https://laligafantasy.relevo.com/"
}

players_found = {}

def extract_player(player_master, market_value=None):
    if not isinstance(player_master, dict):
        return
    pid = str(player_master.get('id', ''))
    if not pid:
        return
    value = market_value or player_master.get('marketValue', 0)
    players_found[pid] = {
        'laliga_fantasy_id': pid,
        'nickname': player_master.get('nickname', ''),
        'name': player_master.get('name', ''),
        'team_id': player_master.get('teamId', ''),
        'position_id': player_master.get('positionId', ''),
        'market_value': value,
    }

# FUENTE 1: Mercado de la liga
print("\n--- Mercado de la liga ---")
r = requests.get(f"https://api-fantasy.llt-services.com/api/v3/league/{LEAGUE_ID}/market?x-lang=es", headers=headers)
for item in r.json():
    pm = item.get('playerMaster', {})
    mv = item.get('marketValue') or pm.get('marketValue', 0)
    extract_player(pm, mv)
print(f"  Jugadores: {len(players_found)}")

# FUENTE 2: Ranking → team IDs
print("\n--- Lineups y equipos ---")
r = requests.get(f"https://api-fantasy.llt-services.com/api/v4/leagues/{LEAGUE_ID}/ranking?x-lang=es", headers=headers)
team_ids = [entry['team']['id'] for entry in r.json()]
print(f"  {len(team_ids)} equipos")

for team_id in team_ids:
    # Lineup
    r = requests.get(f"https://api-fantasy.llt-services.com/api/v3/teams/{team_id}/lineup?x-lang=es", headers=headers)
    if r.status_code == 200:
        formation = r.json().get('formation', {})
        for position_group in formation.values():
            if not isinstance(position_group, list):
                continue
            for player in position_group:
                if not isinstance(player, dict):
                    continue
                extract_player(player.get('playerMaster', {}))

    # Equipo completo
    r = requests.get(f"https://api-fantasy.llt-services.com/api/v4/leagues/{LEAGUE_ID}/teams/{team_id}?x-lang=es", headers=headers)
    if r.status_code == 200:
        for p in r.json().get('players', []):
            if not isinstance(p, dict):
                continue
            pm = p.get('playerMaster', {})
            mv = p.get('marketValue') or pm.get('marketValue', 0)
            extract_player(pm, mv)

    time.sleep(0.2)

print(f"\n✅ TOTAL JUGADORES ÚNICOS: {len(players_found)}")
print("\nEjemplos:")
for pid, p in list(players_found.items())[:10]:
    print(f"  ID {pid}: {p['nickname']} ({p['name']}) valor:{p['market_value']:,}")

with open('laliga_fantasy_players.json', 'w', encoding='utf-8') as f:
    json.dump(list(players_found.values()), f, ensure_ascii=False, indent=2)
print("\nGuardado en laliga_fantasy_players.json")