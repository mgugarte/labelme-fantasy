# En tu entorno Conda
import requests
import pymysql
from datetime import datetime
import os
from dotenv import load_dotenv


# Cargar variables de entorno desde archivo .env
load_dotenv()

# === CONFIG SEGURA ===
API_KEY = os.getenv('FOOTBALL_API_KEY')
LEAGUE_ID = 140
SEASON = 2025

# === CONEXIÓN A LA BBDD SEGURA ===
conn = pymysql.connect(
    host=os.getenv('DB_HOST'),
    user=os.getenv('DB_USER'),
    password=os.getenv('DB_PASSWORD'),
    database=os.getenv('DB_NAME'),
    charset="utf8mb4"
)
cursor = conn.cursor()


# === FUNCIONES API ===
def get_team_statistics(team_id):
    """Obtiene las estadísticas del equipo (partidos jugados, etc.)"""
    url = "https://v3.football.api-sports.io/teams/statistics"
    headers = {"x-apisports-key": API_KEY}
    params = {"league": LEAGUE_ID, "season": SEASON, "team": team_id}
    
    r = requests.get(url, headers=headers, params=params)
    data = r.json()
    
    response = data.get("response", {})
    if response:
        fixtures = response.get("fixtures", {})
        played = fixtures.get("played", {})
        return played.get("total", 0)
    
    return 0

def get_player_injuries(player_id):
    """Obtiene las lesiones futuras del jugador (fecha posterior a hoy)"""
    url = "https://v3.football.api-sports.io/injuries"
    headers = {"x-apisports-key": API_KEY}
    params = {"player": player_id, "season": SEASON}
    
    r = requests.get(url, headers=headers, params=params)
    data = r.json()
    
    response = data.get("response", [])
    if not response:
        return 0, None, None
    
    # Fecha actual (solo fecha, sin hora)
    today = datetime.now().date()
    
    # Buscar lesiones con fixture posterior a hoy
    for injury in response:
        fixture = injury.get("fixture", {})
        fixture_date_str = fixture.get("date")
        
        if fixture_date_str:
            try:
                # Extraer solo los primeros 10 caracteres (YYYY-MM-DD)
                fixture_date_only = fixture_date_str[:10]
                # Convertir a objeto date
                fixture_date = datetime.strptime(fixture_date_only, "%Y-%m-%d").date()
                
                # Si el fixture es posterior o igual a hoy
                if fixture_date >= today:
                    player_info = injury.get("player", {})
                    injury_type = player_info.get("type")
                    injury_reason = player_info.get("reason")
                    return 1, injury_type, injury_reason
            except:
                continue
    
    return 0, None, None

def get_players_by_team(team_id):
    """Obtiene todos los jugadores del equipo con stats"""
    url = "https://v3.football.api-sports.io/players"
    headers = {"x-apisports-key": API_KEY}

    all_players = []
    page = 1
    while True:
        params = {"team": team_id, "season": SEASON, "page": page}
        r = requests.get(url, headers=headers, params=params)
        data = r.json()

        response = data.get("response", [])
        if not response:
            break

        all_players.extend(response)

        paging = data.get("paging", {})
        if paging.get("current") >= paging.get("total", 1):
            break
        page += 1

    return all_players

# === PROCESO PRINCIPAL ===
# Primero obtener todos los equipos
url_teams = "https://v3.football.api-sports.io/teams"
params = {"league": LEAGUE_ID, "season": SEASON}
headers = {"x-apisports-key": API_KEY}
teams = requests.get(url_teams, headers=headers, params=params).json().get("response", [])

# === BUCLE PRINCIPAL ===
for t in teams:
    team_id = t["team"]["id"]
    team_name = t["team"]["name"]
    print(f"Procesando equipo: {team_name}")

    # Obtener partidos jugados por el equipo
    partidos_equipo = get_team_statistics(team_id)
    print(f"  Partidos jugados por {team_name}: {partidos_equipo}")

    players = get_players_by_team(team_id)
    if not players:
        continue

    for p in players:
        player = p["player"]
        stats_list = p.get("statistics", [])

        # --- DATOS DEL JUGADOR ---
        id = player.get("id")
        nombre = player.get("name")
        nombre_1 = player.get("firstname")
        apellido = player.get("lastname")
        edad = player.get("age")
        nacionalidad = player.get("nationality")
        altura = player.get("height")
        peso = player.get("weight")
        foto = player.get("photo")
        
        # Consultar lesiones futuras
        baja, tipo_baja, razon_baja = get_player_injuries(id)

        # --- BUSCAR ESTADÍSTICAS DE LA LIGA ---
        stats = next((s for s in stats_list if s.get("league", {}).get("id") == LEAGUE_ID), None)

        # Valores por defecto
        posicion = None
        minutos = partidos = titular = suplente = 0
        rating = 0.0
        amarillas = rojas = sancionado = 0
        shots_total = shots_on = 0
        goals_total = goals_assists = 0
        passes_total = passes_key = 0
        passes_accuracy = 0.0
        tackles_total = tackles_blocks = tackles_interceptions = 0
        duels_total = duels_won = 0
        dribbles_attempts = dribbles_success = 0
        fouls_drawn = fouls_committed = 0
        penalty_won = penalty_commited = penalty_scored = penalty_missed = 0
        sub_in = sub_out = sub_bench = 0
        team_id_db = None
        team_name_db = None
        team_logo = None
        league_id_db = None
        league_name_db = None
        
        # Nuevos campos de porcentajes
        porcentaje_titularidades = 0.0
        porcentaje_participacion = 0.0

        if stats:
            games = stats.get("games", {})
            cards = stats.get("cards", {})
            dribbles = stats.get("dribbles", {})
            duels = stats.get("duels", {})
            fouls = stats.get("fouls", {})
            goals = stats.get("goals", {})
            league_info = stats.get("league", {})
            passes = stats.get("passes", {})
            penalty = stats.get("penalty", {})
            shots = stats.get("shots", {})
            substitutes = stats.get("substitutes", {})
            tackles = stats.get("tackles", {})
            team_info = stats.get("team", {})

            # Estadísticas
            posicion = games.get("position")
            minutos = games.get("minutes") or 0
            partidos = games.get("appearences") or 0
            titular = games.get("lineups") or 0
            suplente = partidos - titular
            rating = games.get("rating")
            try:
                rating = float(rating) if rating else 0.0
            except:
                rating = 0.0

            # Calcular porcentajes si el equipo ha jugado partidos
            if partidos_equipo > 0:
                porcentaje_titularidades = round((titular / partidos_equipo) * 100, 2)
                porcentaje_participacion = round((partidos / partidos_equipo) * 100, 2)

            amarillas = cards.get("yellow") or 0
            rojas = cards.get("red") or 0
            sancionado = 1 if (rojas > 0 or amarillas >= 5) else 0

            # Shots
            shots_total = shots.get("total") or 0
            shots_on = shots.get("on") or 0

            # Goals
            goals_total = goals.get("total") or 0
            goals_assists = goals.get("assists") or 0

            # Passes
            passes_total = passes.get("total") or 0
            passes_key = passes.get("key") or 0
            passes_accuracy = passes.get("accuracy")
            passes_accuracy = float(passes_accuracy) if passes_accuracy else 0.0

            # Tackles
            tackles_total = tackles.get("total") or 0
            tackles_blocks = tackles.get("blocks") or 0
            tackles_interceptions = tackles.get("interceptions") or 0

            # Duels
            duels_total = duels.get("total") or 0
            duels_won = duels.get("won") or 0

            # Dribbles
            dribbles_attempts = dribbles.get("attempts") or 0
            dribbles_success = dribbles.get("success") or 0

            # Fouls
            fouls_drawn = fouls.get("drawn") or 0
            fouls_committed = fouls.get("committed") or 0

            # Penalty
            penalty_won = penalty.get("won") or 0
            penalty_commited = penalty.get("commited") or 0
            penalty_scored = penalty.get("scored") or 0
            penalty_missed = penalty.get("missed") or 0

            # Substitutes
            sub_in = substitutes.get("in") or 0
            sub_out = substitutes.get("out") or 0
            sub_bench = substitutes.get("bench") or 0

            # Team & league
            team_id_db = team_info.get("id")
            team_name_db = team_info.get("name")
            team_logo = team_info.get("logo")
            league_id_db = league_info.get("id")
            league_name_db = league_info.get("name")

        actualizado = datetime.now()

        # === SQL INSERT / UPDATE ===
        sql = """
        INSERT INTO jugadores_laliga (
            id, nombre, nombre_1, apellido, edad, nacionalidad, altura, peso, foto,
            baja, tipo_baja, razon_baja, posicion, minutos, partidos, titular, suplente, rating,
            porcentaje_titularidades, porcentaje_participacion,
            amarillas, rojas, sancionado,
            shots_total, shots_on,
            goles_total, goles_asistencias,
            passes_total, passes_key, passes_accuracy,
            tackles_total, tackles_blocks, tackles_interceptions,
            duels_total, duels_won,
            dribbles_attempts, dribbles_success,
            fouls_drawn, fouls_committed,
            penalty_won, penalty_commited, penalty_scored, penalty_missed,
            sub_in, sub_out, sub_bench,
            team_id, team_name, team_logo,
            league_id, league_name,
            actualizado, season
        ) VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,
                  %s,%s,%s,%s,%s,%s,%s,%s,%s,
                  %s,%s,
                  %s,%s,%s,
                  %s,%s,
                  %s,%s,
                  %s,%s,%s,
                  %s,%s,%s,
                  %s,%s,
                  %s,%s,
                  %s,%s,
                  %s,%s,%s,%s,
                  %s,%s,%s,
                  %s,%s,%s,
                  %s,%s,
                  %s,%s)
        ON DUPLICATE KEY UPDATE
            nombre=VALUES(nombre),
            nombre_1=VALUES(nombre_1),
            apellido=VALUES(apellido),
            edad=VALUES(edad),
            nacionalidad=VALUES(nacionalidad),
            altura=VALUES(altura),
            peso=VALUES(peso),
            foto=VALUES(foto),
            baja=VALUES(baja),
            tipo_baja=VALUES(tipo_baja),
            razon_baja=VALUES(razon_baja),
            posicion=VALUES(posicion),
            minutos=VALUES(minutos),
            partidos=VALUES(partidos),
            titular=VALUES(titular),
            suplente=VALUES(suplente),
            rating=VALUES(rating),
            porcentaje_titularidades=VALUES(porcentaje_titularidades),
            porcentaje_participacion=VALUES(porcentaje_participacion),
            amarillas=VALUES(amarillas),
            rojas=VALUES(rojas),
            sancionado=VALUES(sancionado),
            shots_total=VALUES(shots_total),
            shots_on=VALUES(shots_on),
            goles_total=VALUES(goles_total),
            goles_asistencias=VALUES(goles_asistencias),
            passes_total=VALUES(passes_total),
            passes_key=VALUES(passes_key),
            passes_accuracy=VALUES(passes_accuracy),
            tackles_total=VALUES(tackles_total),
            tackles_blocks=VALUES(tackles_blocks),
            tackles_interceptions=VALUES(tackles_intercepciones),
            duels_total=VALUES(duels_total),
            duels_won=VALUES(duels_won),
            dribbles_attempts=VALUES(dribbles_attempts),
            dribbles_success=VALUES(dribbles_success),
            fouls_drawn=VALUES(fouls_drawn),
            fouls_committed=VALUES(fouls_committed),
            penalty_won=VALUES(penalty_won),
            penalty_commited=VALUES(penalty_commited),
            penalty_scored=VALUES(penalty_scored),
            penalty_missed=VALUES(penalty_missed),
            sub_in=VALUES(sub_in),
            sub_out=VALUES(sub_out),
            sub_bench=VALUES(sub_bench),
            team_id=VALUES(team_id),
            team_name=VALUES(team_name),
            team_logo=VALUES(team_logo),
            league_id=VALUES(league_id),
            league_name=VALUES(league_name),
            actualizado=VALUES(actualizado),
            season=VALUES(season)
        """
        cursor.execute(sql, (
            id, nombre, nombre_1, apellido, edad, nacionalidad, altura, peso, foto,
            baja, tipo_baja, razon_baja, posicion, minutos, partidos, titular, suplente, rating,
            porcentaje_titularidades, porcentaje_participacion,
            amarillas, rojas, sancionado,
            shots_total, shots_on,
            goals_total, goals_assists,
            passes_total, passes_key, passes_accuracy,
            tackles_total, tackles_blocks, tackles_interceptions,
            duels_total, duels_won,
            dribbles_attempts, dribbles_success,
            fouls_drawn, fouls_committed,
            penalty_won, penalty_commited, penalty_scored, penalty_missed,
            sub_in, sub_out, sub_bench,
            team_id_db, team_name_db, team_logo,
            league_id_db, league_name_db,
            actualizado, SEASON
        ))
        
# === CERRAR CONEXIÓN ===

# === ELIMINAR REGISTROS ===
sql2 = "DELETE FROM jugadores_laliga WHERE team_logo IS NULL"
cursor.execute(sql2)

conn.commit()
conn.close()
print("Actualizacion completada con todos los datos de jugadores")

# Lanzar deploy automático si está activo en .env
from dotenv import load_dotenv
load_dotenv()
if os.getenv('DEPLOY_AFTER', 'false').lower() in ('1','true','yes'):
    try:
        from deploy import deploy_sftp
        deploy_sftp.upload()
        print("Despliegue SFTP completado.")
    except Exception as e:
        print("Error al desplegar por SFTP:", e)