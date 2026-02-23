import requests
import pymysql
from datetime import datetime
import os
from dotenv import load_dotenv
import time
import sys
import io

# Configurar encoding UTF-8 para Windows
if sys.platform == 'win32':
    sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')
    sys.stderr = io.TextIOWrapper(sys.stderr.buffer, encoding='utf-8')

# Cargar variables de entorno
load_dotenv()

# === CONFIGURACIÓN ===
API_KEY = os.getenv('FOOTBALL_API_KEY')
LEAGUE_ID = 140  # LaLiga
SEASON = 2025

# Configuración de rate limiting (API-Football tiene límites)
REQUEST_DELAY = 0.5  # Segundos entre requests
MAX_RETRIES = 3

# === CONEXIÓN A LA BBDD ===
def get_db_connection():
    """Crea y retorna una conexión a la base de datos"""
    return pymysql.connect(
        host=os.getenv('DB_HOST'),
        user=os.getenv('DB_USER'),
        password=os.getenv('DB_PASSWORD'),
        database=os.getenv('DB_NAME'),
        charset="utf8mb4"
    )

# === FUNCIONES API ===
def api_request(url, params, retries=MAX_RETRIES):
    """
    Realiza una petición a la API con reintentos y manejo de errores
    """
    headers = {"x-apisports-key": API_KEY}
    
    for attempt in range(retries):
        try:
            time.sleep(REQUEST_DELAY)  # Rate limiting
            response = requests.get(url, headers=headers, params=params, timeout=15)
            
            if response.status_code == 200:
                data = response.json()
                
                # Verificar errores en la respuesta
                errors = data.get("errors", {})
                if errors and len(errors) > 0:
                    print(f"⚠️  API Error: {errors}")
                    return None
                
                return data
            
            elif response.status_code == 429:  # Too Many Requests
                wait_time = 60 * (attempt + 1)
                print(f"⏳ Rate limit alcanzado. Esperando {wait_time}s...")
                time.sleep(wait_time)
                continue
            
            else:
                print(f"❌ HTTP Error {response.status_code}: {response.text}")
                return None
                
        except requests.exceptions.Timeout:
            print(f"⏱️  Timeout en intento {attempt + 1}/{retries}")
            if attempt < retries - 1:
                time.sleep(5)
                continue
            return None
            
        except Exception as e:
            print(f"❌ Error en petición API: {str(e)}")
            return None
    
    return None

def get_all_fixtures():
    """
    Obtiene todos los fixtures de LaLiga temporada 2025
    Retorna: lista de fixtures con {id, date, teams, status}
    """
    url = "https://v3.football.api-sports.io/fixtures"
    params = {
        "league": LEAGUE_ID,
        "season": SEASON
    }
    
    print(f"\n📅 Obteniendo fixtures de LaLiga {SEASON}...")
    data = api_request(url, params)
    
    if not data:
        print("❌ No se pudieron obtener los fixtures")
        return []
    
    fixtures = data.get("response", [])
    print(f"✅ {len(fixtures)} fixtures encontrados")
    
    # Filtrar solo partidos finalizados
    finished_fixtures = []
    for fixture in fixtures:
        status = fixture.get("fixture", {}).get("status", {}).get("short")
        if status in ["FT", "AET", "PEN"]:  # Full Time, After Extra Time, Penalties
            fixture_id = fixture["fixture"]["id"]
            fixture_date = fixture["fixture"]["date"][:10]  # YYYY-MM-DD
            home_team = fixture["teams"]["home"]
            away_team = fixture["teams"]["away"]
            
            finished_fixtures.append({
                "id": fixture_id,
                "date": fixture_date,
                "home_team_id": home_team["id"],
                "home_team_name": home_team["name"],
                "away_team_id": away_team["id"],
                "away_team_name": away_team["name"]
            })
    
    print(f"✅ {len(finished_fixtures)} partidos finalizados para procesar")
    return finished_fixtures

def get_fixture_players(fixture_id, team_id):
    """
    Obtiene las estadísticas de jugadores de un equipo en un fixture específico
    """
    url = "https://v3.football.api-sports.io/fixtures/players"
    params = {
        "fixture": fixture_id,
        "team": team_id
    }
    
    data = api_request(url, params)
    
    if not data:
        return None
    
    response = data.get("response", [])
    if not response:
        return None
    
    return response[0]  # Retorna el objeto con team + players

def safe_int(value, default=0):
    """Convierte valor a int de forma segura"""
    if value is None:
        return default
    try:
        return int(value)
    except:
        return default

def safe_float(value, default=0.0):
    """Convierte valor a float de forma segura"""
    if value is None:
        return default
    try:
        return float(value)
    except:
        return default

def insert_player_fixture_stats(cursor, fixture_id, fixture_date, team_data, opponent_team_id, opponent_team_name):
    """
    Inserta las estadísticas de todos los jugadores de un equipo en un fixture
    """
    team_info = team_data.get("team", {})
    team_id = team_info.get("id")
    team_name = team_info.get("name")
    
    players = team_data.get("players", [])
    
    inserted = 0
    updated = 0
    
    for player_data in players:
        player = player_data.get("player", {})
        stats_list = player_data.get("statistics", [])
        
        if not stats_list:
            continue
        
        stats = stats_list[0]  # Tomar el primer objeto de estadísticas
        
        # Extraer información del jugador
        player_id = player.get("id")
        player_name = player.get("name")
        
        # Extraer estadísticas del juego
        games = stats.get("games", {})
        goals_data = stats.get("goals", {})
        shots_data = stats.get("shots", {})
        passes_data = stats.get("passes", {})
        tackles_data = stats.get("tackles", {})
        duels_data = stats.get("duels", {})
        dribbles_data = stats.get("dribbles", {})
        fouls_data = stats.get("fouls", {})
        cards_data = stats.get("cards", {})
        penalty_data = stats.get("penalty", {})
        
        # Preparar valores
        minutes = safe_int(games.get("minutes"), 0)
        number = safe_int(games.get("number"))
        position = games.get("position") or "SUB"
        rating = safe_float(games.get("rating"), 0.0)
        captain = 1 if games.get("captain") else 0
        substitute = 1 if games.get("substitute") else 0
        
        # Goles y asistencias
        goals = safe_int(goals_data.get("total"), 0)
        assists = safe_int(goals_data.get("assists"), 0)
        goals_conceded = safe_int(goals_data.get("conceded"), 0)
        saves = safe_int(goals_data.get("saves"), 0)
        
        # Disparos
        shots_total = safe_int(shots_data.get("total"), 0)
        shots_on = safe_int(shots_data.get("on"), 0)
        
        # Pases
        passes_total = safe_int(passes_data.get("total"), 0)
        passes_key = safe_int(passes_data.get("key"), 0)
        passes_accuracy = safe_int(passes_data.get("accuracy"), 0)
        
        # Defensivas
        tackles_total = safe_int(tackles_data.get("total"), 0)
        blocks = safe_int(tackles_data.get("blocks"), 0)
        interceptions = safe_int(tackles_data.get("interceptions"), 0)
        
        # Duelos y regates
        duels_total = safe_int(duels_data.get("total"), 0)
        duels_won = safe_int(duels_data.get("won"), 0)
        dribbles_attempts = safe_int(dribbles_data.get("attempts"), 0)
        dribbles_success = safe_int(dribbles_data.get("success"), 0)
        dribbles_past = safe_int(dribbles_data.get("past"), 0)
        
        # Faltas y tarjetas
        fouls_drawn = safe_int(fouls_data.get("drawn"), 0)
        fouls_committed = safe_int(fouls_data.get("committed"), 0)
        yellow_cards = safe_int(cards_data.get("yellow"), 0)
        red_cards = safe_int(cards_data.get("red"), 0)
        
        # Penales
        penalty_won = safe_int(penalty_data.get("won"), 0)
        penalty_committed = safe_int(penalty_data.get("commited"), 0)
        penalty_scored = safe_int(penalty_data.get("scored"), 0)
        penalty_missed = safe_int(penalty_data.get("missed"), 0)
        penalty_saved = safe_int(penalty_data.get("saved"), 0)
        
        # Offsides
        offsides = safe_int(stats.get("offsides"), 0)
        
        # SQL INSERT con ON DUPLICATE KEY UPDATE
        sql = """
        INSERT INTO jugadores_fixtures_laliga (
            fixture_id, player_id, team_id, team_name,
            fixture_date, opponent_team_id, opponent_team_name,
            minutes, number, position, rating, captain, substitute,
            goals, assists, goals_conceded, saves,
            shots_total, shots_on,
            passes_total, passes_key, passes_accuracy,
            tackles_total, blocks, interceptions,
            duels_total, duels_won,
            dribbles_attempts, dribbles_success, dribbles_past,
            fouls_drawn, fouls_committed,
            yellow_cards, red_cards,
            penalty_won, penalty_committed, penalty_scored, penalty_missed, penalty_saved,
            offsides,
            season, actualizado
        ) VALUES (
            %s, %s, %s, %s,
            %s, %s, %s,
            %s, %s, %s, %s, %s, %s,
            %s, %s, %s, %s,
            %s, %s,
            %s, %s, %s,
            %s, %s, %s,
            %s, %s,
            %s, %s, %s,
            %s, %s,
            %s, %s,
            %s, %s, %s, %s, %s,
            %s,
            %s, %s
        )
        ON DUPLICATE KEY UPDATE
            minutes = VALUES(minutes),
            number = VALUES(number),
            position = VALUES(position),
            rating = VALUES(rating),
            captain = VALUES(captain),
            substitute = VALUES(substitute),
            goals = VALUES(goals),
            assists = VALUES(assists),
            goals_conceded = VALUES(goals_conceded),
            saves = VALUES(saves),
            shots_total = VALUES(shots_total),
            shots_on = VALUES(shots_on),
            passes_total = VALUES(passes_total),
            passes_key = VALUES(passes_key),
            passes_accuracy = VALUES(passes_accuracy),
            tackles_total = VALUES(tackles_total),
            blocks = VALUES(blocks),
            interceptions = VALUES(interceptions),
            duels_total = VALUES(duels_total),
            duels_won = VALUES(duels_won),
            dribbles_attempts = VALUES(dribbles_attempts),
            dribbles_success = VALUES(dribbles_success),
            dribbles_past = VALUES(dribbles_past),
            fouls_drawn = VALUES(fouls_drawn),
            fouls_committed = VALUES(fouls_committed),
            yellow_cards = VALUES(yellow_cards),
            red_cards = VALUES(red_cards),
            penalty_won = VALUES(penalty_won),
            penalty_committed = VALUES(penalty_committed),
            penalty_scored = VALUES(penalty_scored),
            penalty_missed = VALUES(penalty_missed),
            penalty_saved = VALUES(penalty_saved),
            offsides = VALUES(offsides),
            actualizado = VALUES(actualizado)
        """
        
        actualizado = datetime.now()
        
        try:
            cursor.execute(sql, (
                fixture_id, player_id, team_id, team_name,
                fixture_date, opponent_team_id, opponent_team_name,
                minutes, number, position, rating, captain, substitute,
                goals, assists, goals_conceded, saves,
                shots_total, shots_on,
                passes_total, passes_key, passes_accuracy,
                tackles_total, blocks, interceptions,
                duels_total, duels_won,
                dribbles_attempts, dribbles_success, dribbles_past,
                fouls_drawn, fouls_committed,
                yellow_cards, red_cards,
                penalty_won, penalty_committed, penalty_scored, penalty_missed, penalty_saved,
                offsides,
                SEASON, actualizado
            ))
            
            if cursor.rowcount == 1:
                inserted += 1
            else:
                updated += 1
                
        except Exception as e:
            print(f"❌ Error insertando {player_name}: {str(e)}")
            continue
    
    return inserted, updated

# === PROCESO PRINCIPAL ===
def main():
    """Función principal del script"""
    
    print("=" * 60)
    print("🚀 ACTUALIZACIÓN DE ESTADÍSTICAS POR FIXTURE")
    print(f"   LaLiga {SEASON}")
    print("=" * 60)
    
    # Conectar a la base de datos
    try:
        conn = get_db_connection()
        cursor = conn.cursor()
        print("✅ Conexión a base de datos establecida")
    except Exception as e:
        print(f"❌ Error conectando a la base de datos: {str(e)}")
        sys.exit(1)
    
    # Obtener todos los fixtures
    fixtures = get_all_fixtures()
    
    if not fixtures:
        print("❌ No hay fixtures para procesar")
        conn.close()
        return
    
    # Contadores globales
    total_inserted = 0
    total_updated = 0
    total_fixtures_processed = 0
    total_fixtures_failed = 0
    
    # Procesar cada fixture
    for i, fixture in enumerate(fixtures, 1):
        fixture_id = fixture["id"]
        fixture_date = fixture["date"]
        home_team_id = fixture["home_team_id"]
        home_team_name = fixture["home_team_name"]
        away_team_id = fixture["away_team_id"]
        away_team_name = fixture["away_team_name"]
        
        print(f"\n{'='*60}")
        print(f"🏟️  [{i}/{len(fixtures)}] Fixture {fixture_id} - {fixture_date}")
        print(f"   {home_team_name} vs {away_team_name}")
        print(f"{'='*60}")
        
        fixture_success = True
        
        # Procesar equipo local
        print(f"🏠 Procesando {home_team_name}...")
        home_data = get_fixture_players(fixture_id, home_team_id)
        
        if home_data:
            ins, upd = insert_player_fixture_stats(
                cursor, fixture_id, fixture_date, home_data,
                away_team_id, away_team_name
            )
            print(f"   ✅ {ins} insertados, {upd} actualizados")
            total_inserted += ins
            total_updated += upd
        else:
            print(f"   ⚠️  No se pudieron obtener datos")
            fixture_success = False
        
        # Procesar equipo visitante
        print(f"✈️  Procesando {away_team_name}...")
        away_data = get_fixture_players(fixture_id, away_team_id)
        
        if away_data:
            ins, upd = insert_player_fixture_stats(
                cursor, fixture_id, fixture_date, away_data,
                home_team_id, home_team_name
            )
            print(f"   ✅ {ins} insertados, {upd} actualizados")
            total_inserted += ins
            total_updated += upd
        else:
            print(f"   ⚠️  No se pudieron obtener datos")
            fixture_success = False
        
        # Commit después de cada fixture
        try:
            conn.commit()
            if fixture_success:
                total_fixtures_processed += 1
                print(f"✅ Fixture {fixture_id} procesado correctamente")
            else:
                total_fixtures_failed += 1
                print(f"⚠️  Fixture {fixture_id} procesado con errores")
        except Exception as e:
            print(f"❌ Error en commit: {str(e)}")
            conn.rollback()
            total_fixtures_failed += 1
    
    # Cerrar conexión
    conn.close()
    
    # Resumen final
    print("\n" + "=" * 60)
    print("📊 RESUMEN DE ACTUALIZACIÓN")
    print("=" * 60)
    print(f"✅ Fixtures procesados exitosamente: {total_fixtures_processed}")
    print(f"⚠️  Fixtures con errores: {total_fixtures_failed}")
    print(f"📥 Total registros insertados: {total_inserted}")
    print(f"🔄 Total registros actualizados: {total_updated}")
    print(f"📊 Total registros afectados: {total_inserted + total_updated}")
    print("=" * 60)
    print(f"✅ Actualización completada - {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print("=" * 60)

if __name__ == "__main__":
    main()
# %%
