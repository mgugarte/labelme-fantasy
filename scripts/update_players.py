# En tu entorno Conda
import requests
import pymysql
from datetime import datetime
import os
import math
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
    charset="utf8mb4",
    cursorclass=pymysql.cursors.DictCursor
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
    """Obtiene las lesiones del jugador basándose en el fixture más reciente"""
    url = "https://v3.football.api-sports.io/injuries"
    headers = {"x-apisports-key": API_KEY}
    params = {"player": player_id, "season": SEASON}

    r = requests.get(url, headers=headers, params=params)
    data = r.json()

    response = data.get("response", [])
    if not response:
        return 0, None, None

    today = datetime.now().date()

    # Buscar el fixture MÁS RECIENTE de todas las lesiones
    latest_injury = None
    latest_fixture_date = None

    for injury in response:
        fixture = injury.get("fixture", {})
        fixture_date_str = fixture.get("date")

        if fixture_date_str:
            try:
                # Extraer solo la fecha (YYYY-MM-DD)
                fixture_date_only = fixture_date_str[:10]
                fixture_date = datetime.strptime(fixture_date_only, "%Y-%m-%d").date()

                # Guardar si es el fixture más reciente encontrado hasta ahora
                if latest_fixture_date is None or fixture_date > latest_fixture_date:
                    latest_fixture_date = fixture_date
                    latest_injury = injury
            except:
                continue

    # Si el fixture más reciente es HOY o FUTURO → está de BAJA
    if latest_injury and latest_fixture_date >= today:
        player_info = latest_injury.get("player", {})
        injury_type = player_info.get("type")
        injury_reason = player_info.get("reason")
        return 1, injury_type, injury_reason

    # Si el fixture más reciente es PASADO → está disponible
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

# === MAPEO DE POSICIONES ===
POSICIONES_ES = {
    'Goalkeeper': 'Portero',
    'Defender': 'Defensa',
    'Midfielder': 'Centrocampista',
    'Attacker': 'Delantero'
}

# === TRADUCCIÓN DE TIPOS DE BAJA ===
TIPOS_BAJA_ES = {
    'Missing Fixture': 'Sin Convocatoria',
    'Questionable': 'Duda',
    'Suspended': 'Sancionado',
    'Yellow Cards': 'Acumulación Tarjetas',
    'Red Card': 'Tarjeta Roja',
    'Injury': 'Lesionado',
    "Coach's decision": 'Decisión Técnica',
    'Coach Decision': 'Decisión Técnica',
    'International Duty': 'Selección',
    'Not Available': 'No Disponible'
}

# === TRADUCCIÓN DE RAZONES DE BAJA ===
RAZONES_BAJA_ES = {
    # Lesiones musculares
    'Muscle Injury': 'Lesión Muscular',
    'Thigh Injury': 'Lesión en el Muslo',
    'Hamstring Injury': 'Lesión Isquiotibiales',
    'Calf Injury': 'Lesión en Pantorrilla',
    'Groin Injury': 'Lesión en la Ingle',

    # Lesiones articulares
    'Knee Injury': 'Lesión de Rodilla',
    'Ankle Injury': 'Lesión de Tobillo',
    'Hip Injury': 'Lesión de Cadera',
    'Achilles Tendon Injury': 'Lesión Tendón de Aquiles',

    # Otras lesiones
    'Back Injury': 'Lesión de Espalda',
    'Shoulder Injury': 'Lesión de Hombro',
    'Foot Injury': 'Lesión en el Pie',
    'Head Injury': 'Lesión en la Cabeza',
    'Concussion': 'Conmoción',
    'Injury': 'Lesión',
    'Knock': 'Golpe',
    'Broken nose': 'Nariz Rota',
    'Broken ankle': 'Tobillo Roto',
    'Jumpers knee': 'Rodilla de Saltador',

    # Sanciones
    'Suspended': 'Suspendido',
    'Yellow Cards': 'Acumulación de Amarillas',
    'Accumulation of Yellow Cards': 'Acumulación de Amarillas',
    'Red Card': 'Tarjeta Roja',

    # Otros motivos
    "Coach's decision": 'Decisión del Entrenador',
    'Coach Decision': 'Decisión del Entrenador',
    'International Duty': 'Concentración con Selección',
    'Illness': 'Enfermedad',
    'Personal Reasons': 'Motivos Personales',
    'Inactive': 'Inactivo',
    'Rest': 'Descanso',
    'Unknown': 'Desconocido'
}

def traducir_tipo_baja(tipo):
    """Traduce el tipo de baja del inglés al español"""
    if not tipo:
        return None
    return TIPOS_BAJA_ES.get(tipo, tipo)

def traducir_razon_baja(razon):
    """Traduce la razón de baja del inglés al español"""
    if not razon:
        return None
    return RAZONES_BAJA_ES.get(razon, razon)

# === CONFIGURACIÓN ALGORITMO RECOMENDACIÓN FANTASY ===
PESOS_COMPONENTES = {
    'tendencia_reciente': 0.40,
    'consistencia': 0.25,
    'calidad_rendimiento': 0.25,
    'estado_fisico': 0.10
}

PESOS_PARTIDOS = [0.40, 0.25, 0.17, 0.11, 0.07]

UMBRALES_POSICION = {
    'Portero': {'excelente': 7.0, 'bueno': 6.5, 'aceptable': 6.0},
    'Defensa': {'excelente': 7.0, 'bueno': 6.7, 'aceptable': 6.3},
    'Centrocampista': {'excelente': 7.2, 'bueno': 6.8, 'aceptable': 6.5},
    'Delantero': {'excelente': 7.5, 'bueno': 7.0, 'aceptable': 6.5}
}

def calcular_recomendacion_fantasy(player_id, cursor_obj):
    """
    Calcula la recomendación fantasy con algoritmo mejorado v2

    Args:
        player_id: ID del jugador
        cursor_obj: Cursor de pymysql para consultas

    Returns:
        int: Recomendación 0-100%
    """
    # Obtener datos del jugador
    cursor_obj.execute("""
        SELECT baja, posicion, rating AS rating_total,
               porcentaje_titularidades
        FROM jugadores_laliga
        WHERE id = %s AND season = %s
    """, (player_id, SEASON))

    jugador = cursor_obj.fetchone()

    if not jugador:
        return 0

    # Si está de baja → 0%
    if jugador['baja'] == 1:
        return 0

    # Obtener últimos 5 partidos
    cursor_obj.execute("""
        SELECT substitute, rating, minutes, fixture_date
        FROM jugadores_fixtures_laliga
        WHERE player_id = %s AND season = %s
        ORDER BY fixture_date DESC
        LIMIT 5
    """, (player_id, SEASON))

    ultimos_5 = cursor_obj.fetchall()

    # Si no tiene partidos recientes, usar histórico como fallback
    if len(ultimos_5) == 0:
        return min(int(jugador['porcentaje_titularidades'] or 0), 50)

    # COMPONENTE 1: TENDENCIA RECIENTE (40%)
    tendencia_score = 0

    for i, partido in enumerate(ultimos_5):
        peso = PESOS_PARTIDOS[i]

        if partido['substitute'] == 0 and partido['minutes'] > 0:
            # Titular
            minutos_score = min(partido['minutes'] / 90 * 100, 100)
            tendencia_score += minutos_score * peso
        elif partido['minutes'] > 0:
            # Suplente que jugó
            minutos_score = (partido['minutes'] / 90) * 60
            tendencia_score += minutos_score * peso

    # COMPONENTE 2: CONSISTENCIA (25%)
    ratings_validos = [float(p['rating']) for p in ultimos_5 if p['rating'] and p['rating'] > 0]

    if len(ratings_validos) >= 3:
        media_rating = sum(ratings_validos) / len(ratings_validos)
        varianza = sum((r - media_rating) ** 2 for r in ratings_validos) / len(ratings_validos)
        desv_rating = math.sqrt(varianza)
        consistencia_score = max(0, 100 - (desv_rating / 1.5) * 100)
    else:
        consistencia_score = 50

    # COMPONENTE 3: CALIDAD RENDIMIENTO (25%)
    posicion = jugador['posicion'] or 'Centrocampista'
    umbrales = UMBRALES_POSICION.get(posicion, UMBRALES_POSICION['Centrocampista'])

    if ratings_validos:
        rating_promedio = sum(ratings_validos) / len(ratings_validos)

        if rating_promedio >= umbrales['excelente']:
            calidad_score = 100
        elif rating_promedio >= umbrales['bueno']:
            rango = umbrales['excelente'] - umbrales['bueno']
            progreso = float(rating_promedio) - umbrales['bueno']
            calidad_score = 75 + (progreso / rango) * 25
        elif rating_promedio >= umbrales['aceptable']:
            rango = umbrales['bueno'] - umbrales['aceptable']
            progreso = float(rating_promedio) - umbrales['aceptable']
            calidad_score = 50 + (progreso / rango) * 25
        else:
            calidad_score = max(0, (float(rating_promedio) / umbrales['aceptable']) * 50)
    else:
        calidad_score = 50

    # COMPONENTE 4: ESTADO FÍSICO (10%)
    estado_score = 100

    ultimos_3 = ultimos_5[:3]
    partidos_completos = sum(1 for p in ultimos_3 if p['minutes'] >= 80)
    jugo_algo = any(p['minutes'] > 0 for p in ultimos_3)

    if partidos_completos == 0 and jugo_algo:
        estado_score = 70

    sustituido_temprano = sum(
        1 for p in ultimos_3
        if p['substitute'] == 0 and 0 < p['minutes'] < 60
    )

    if sustituido_temprano >= 2:
        estado_score = 60

    # CÁLCULO FINAL
    recomendacion = (
        tendencia_score * PESOS_COMPONENTES['tendencia_reciente'] +
        consistencia_score * PESOS_COMPONENTES['consistencia'] +
        calidad_score * PESOS_COMPONENTES['calidad_rendimiento'] +
        estado_score * PESOS_COMPONENTES['estado_fisico']
    )

    # Ajuste: Si histórico bajo, limitar recomendación
    porcentaje_historico = jugador['porcentaje_titularidades'] or 0
    if porcentaje_historico < 30:
        recomendacion = min(recomendacion, 70)

    return round(max(0, min(100, recomendacion)))

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

        # Traducir tipo y razón de baja al español
        tipo_baja = traducir_tipo_baja(tipo_baja)
        razon_baja = traducir_razon_baja(razon_baja)

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
            # Traducir posición al español
            if posicion:
                posicion = POSICIONES_ES.get(posicion, posicion)
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
            tackles_interceptions=VALUES(tackles_interceptions),
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
        
# === ELIMINAR REGISTROS ===
sql2 = "DELETE FROM jugadores_laliga WHERE team_logo IS NULL"
cursor.execute(sql2)
conn.commit()

print("\n" + "=" * 60)
print("[OK] Actualizacion de jugadores completada")
print("=" * 60)

# =====================================================
# CALCULAR RECOMENDACIONES FANTASY
# =====================================================
print("\n" + "=" * 60)
print("CALCULANDO RECOMENDACIONES FANTASY")
print("Algoritmo mejorado v2 - 4 componentes")
print("=" * 60)

# Obtener todos los jugadores de la temporada actual
cursor.execute("""
    SELECT id, nombre, posicion, baja
    FROM jugadores_laliga
    WHERE season = %s
    ORDER BY team_name, nombre
""", (SEASON,))

jugadores = cursor.fetchall()
total = len(jugadores)

print(f"\nProcesando {total} jugadores...\n")

actualizados = 0
lesionados = 0
sin_datos = 0

for i, jugador in enumerate(jugadores, 1):
    player_id = jugador['id']
    nombre = jugador['nombre']

    # Calcular recomendación
    recomendacion = calcular_recomendacion_fantasy(player_id, cursor)

    # Actualizar en BD
    cursor.execute("""
        UPDATE jugadores_laliga
        SET recomendacion_fantasy = %s
        WHERE id = %s AND season = %s
    """, (recomendacion, player_id, SEASON))

    # Contadores
    if jugador['baja'] == 1:
        lesionados += 1
    elif recomendacion == 0:
        sin_datos += 1

    actualizados += 1

    # Mostrar progreso cada 50 jugadores
    if i % 50 == 0:
        print(f"Procesados {i}/{total} jugadores...")

# Commit de recomendaciones
conn.commit()

print("\n" + "=" * 60)
print("[OK] RECOMENDACIONES CALCULADAS")
print("=" * 60)
print(f"Total procesados: {actualizados}")
print(f"Lesionados (0%): {lesionados}")
print(f"Sin datos suficientes: {sin_datos}")
print(f"Con recomendación: {actualizados - lesionados - sin_datos}")
print("=" * 60)

# Estadísticas de distribución
cursor.execute("""
    SELECT
        COUNT(CASE WHEN recomendacion_fantasy >= 90 THEN 1 END) as oro,
        COUNT(CASE WHEN recomendacion_fantasy >= 70 AND recomendacion_fantasy < 90 THEN 1 END) as plata,
        COUNT(CASE WHEN recomendacion_fantasy >= 40 AND recomendacion_fantasy < 70 THEN 1 END) as bronce,
        COUNT(CASE WHEN recomendacion_fantasy < 40 AND recomendacion_fantasy > 0 THEN 1 END) as riesgo
    FROM jugadores_laliga
    WHERE season = %s AND baja = 0
""", (SEASON,))

stats = cursor.fetchone()

print("\n[STATS] DISTRIBUCION DE MEDALLAS:")
print(f"  [ORO] (90-100%): {stats['oro']} jugadores")
print(f"  [PLATA] (70-89%): {stats['plata']} jugadores")
print(f"  [BRONCE] (40-69%): {stats['bronce']} jugadores")
print(f"  [RIESGO] (0-39%): {stats['riesgo']} jugadores")
print("=" * 60)

# Cerrar conexión
conn.close()
print("\n[OK] Proceso completo finalizado")

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