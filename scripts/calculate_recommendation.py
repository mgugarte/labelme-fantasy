"""
========================================================
⚠️  OBSOLETO - ESTE SCRIPT YA NO ES NECESARIO
========================================================

Este script ha sido INTEGRADO en update_players.py para mayor
eficiencia y consistencia de datos.

IMPORTANTE:
- Ya NO es necesario ejecutar este script por separado
- El cálculo de recomendaciones se hace automáticamente al
  ejecutar update_players.py o ejecutar_update_players.bat
- Este archivo se mantiene solo como referencia histórica

Para actualizar jugadores Y calcular recomendaciones, ejecuta:
  python update_players.py

O usa el archivo .bat:
  ejecutar_update_players.bat

========================================================
INFORMACIÓN DEL ALGORITMO (ahora en update_players.py)
========================================================
Calcula la recomendación fantasy para cada jugador
basándose en 4 componentes principales:

1. Tendencia reciente (40%): Últimos 5 partidos con pesos exponenciales
2. Consistencia (25%): Estabilidad en el rendimiento
3. Calidad rendimiento (25%): Rating contextualizado por posición
4. Estado físico (10%): Detección de molestias/fatiga

Autor: LabelMe Fantasy
Fecha: 2026-01-14 (Integrado: 2026-01-19)
========================================================
"""

import pymysql
import os
import math
from dotenv import load_dotenv
from datetime import datetime

# Cargar variables de entorno
load_dotenv()

# Conexión a BD
conn = pymysql.connect(
    host=os.getenv('DB_HOST'),
    user=os.getenv('DB_USER'),
    password=os.getenv('DB_PASSWORD'),
    database=os.getenv('DB_NAME'),
    charset="utf8mb4",
    cursorclass=pymysql.cursors.DictCursor
)
cursor = conn.cursor()

SEASON = 2025

# =====================================================
# CONFIGURACIÓN DEL ALGORITMO
# =====================================================

# Pesos de componentes (suman 100%)
PESOS_COMPONENTES = {
    'tendencia_reciente': 0.40,
    'consistencia': 0.25,
    'calidad_rendimiento': 0.25,
    'estado_fisico': 0.10
}

# Pesos exponenciales para últimos 5 partidos
# Más reciente = más peso
PESOS_PARTIDOS = [0.40, 0.25, 0.17, 0.11, 0.07]

# Umbrales de rating por posición
UMBRALES_POSICION = {
    'Portero': {'excelente': 7.0, 'bueno': 6.5, 'aceptable': 6.0},
    'Defensa': {'excelente': 7.0, 'bueno': 6.7, 'aceptable': 6.3},
    'Centrocampista': {'excelente': 7.2, 'bueno': 6.8, 'aceptable': 6.5},
    'Delantero': {'excelente': 7.5, 'bueno': 7.0, 'aceptable': 6.5}
}


def calcular_recomendacion_fantasy(player_id):
    """
    Calcula la recomendación fantasy con algoritmo mejorado v2

    Returns:
        int: Recomendación 0-100%
    """

    # ========================================
    # OBTENER DATOS DEL JUGADOR
    # ========================================
    cursor.execute("""
        SELECT baja, posicion, rating AS rating_total,
               porcentaje_titularidades
        FROM jugadores_laliga
        WHERE id = %s AND season = %s
    """, (player_id, SEASON))

    jugador = cursor.fetchone()

    if not jugador:
        return 0

    # Si está lesionado → 0%
    if jugador['baja'] == 1:
        return 0

    # ========================================
    # OBTENER ÚLTIMOS 5 PARTIDOS
    # ========================================
    cursor.execute("""
        SELECT substitute, rating, minutes, fixture_date
        FROM jugadores_fixtures_laliga
        WHERE player_id = %s AND season = %s
        ORDER BY fixture_date DESC
        LIMIT 5
    """, (player_id, SEASON))

    ultimos_5 = cursor.fetchall()

    # Si no tiene partidos recientes, usar histórico como fallback
    if len(ultimos_5) == 0:
        return min(int(jugador['porcentaje_titularidades'] or 0), 50)

    # ========================================
    # COMPONENTE 1: TENDENCIA RECIENTE (40%)
    # ========================================
    tendencia_score = 0

    for i, partido in enumerate(ultimos_5):
        peso = PESOS_PARTIDOS[i]

        # Puntuación por titularidad y minutos
        if partido['substitute'] == 0 and partido['minutes'] > 0:
            # Titular - puntuación según minutos
            minutos_score = min(partido['minutes'] / 90 * 100, 100)
            tendencia_score += minutos_score * peso
        elif partido['minutes'] > 0:
            # Suplente que jugó - puntuación proporcional (max 60%)
            minutos_score = (partido['minutes'] / 90) * 60
            tendencia_score += minutos_score * peso
        # No jugó = 0 puntos (sin penalización)

    # ========================================
    # COMPONENTE 2: CONSISTENCIA (25%)
    # ========================================
    # Convertir ratings a float para evitar problemas con Decimal de MySQL
    ratings_validos = [float(p['rating']) for p in ultimos_5 if p['rating'] and p['rating'] > 0]

    if len(ratings_validos) >= 3:
        # Calcular desviación estándar manualmente
        media_rating = sum(ratings_validos) / len(ratings_validos)
        varianza = sum((r - media_rating) ** 2 for r in ratings_validos) / len(ratings_validos)
        desv_rating = math.sqrt(varianza)

        # Consistencia inversa a la desviación
        # Desv 0.0 = 100 pts, Desv 1.5 = 0 pts
        consistencia_score = max(0, 100 - (desv_rating / 1.5) * 100)
    else:
        # Pocos datos → puntuar neutro
        consistencia_score = 50

    # ========================================
    # COMPONENTE 3: CALIDAD RENDIMIENTO (25%)
    # ========================================

    # Obtener umbrales según posición
    posicion = jugador['posicion'] or 'Centrocampista'
    umbrales = UMBRALES_POSICION.get(posicion, UMBRALES_POSICION['Centrocampista'])

    if ratings_validos:
        rating_promedio = sum(ratings_validos) / len(ratings_validos)

        if rating_promedio >= umbrales['excelente']:
            calidad_score = 100
        elif rating_promedio >= umbrales['bueno']:
            # Escala lineal entre bueno y excelente
            rango = umbrales['excelente'] - umbrales['bueno']
            progreso = float(rating_promedio) - umbrales['bueno']
            calidad_score = 75 + (progreso / rango) * 25
        elif rating_promedio >= umbrales['aceptable']:
            # Escala lineal entre aceptable y bueno
            rango = umbrales['bueno'] - umbrales['aceptable']
            progreso = float(rating_promedio) - umbrales['aceptable']
            calidad_score = 50 + (progreso / rango) * 25
        else:
            # Por debajo de aceptable
            calidad_score = max(0, (float(rating_promedio) / umbrales['aceptable']) * 50)
    else:
        calidad_score = 50

    # ========================================
    # COMPONENTE 4: ESTADO FÍSICO (10%)
    # ========================================
    estado_score = 100

    # Verificar partidos completos en últimos 3
    ultimos_3 = ultimos_5[:3]
    partidos_completos = sum(1 for p in ultimos_3 if p['minutes'] >= 80)

    # Si no completó ninguno pero sí jugó → posible molestia
    jugo_algo = any(p['minutes'] > 0 for p in ultimos_3)
    if partidos_completos == 0 and jugo_algo:
        estado_score = 70

    # Verificar sustituciones tempranas (titular que juega <60 min)
    sustituido_temprano = sum(
        1 for p in ultimos_3
        if p['substitute'] == 0 and 0 < p['minutes'] < 60
    )

    if sustituido_temprano >= 2:
        estado_score = 60  # Posible gestión de cargas

    # ========================================
    # CÁLCULO FINAL
    # ========================================
    recomendacion = (
        tendencia_score * PESOS_COMPONENTES['tendencia_reciente'] +
        consistencia_score * PESOS_COMPONENTES['consistencia'] +
        calidad_score * PESOS_COMPONENTES['calidad_rendimiento'] +
        estado_score * PESOS_COMPONENTES['estado_fisico']
    )

    # Ajuste: Si histórico bajo, limitar recomendación
    # (evita sobreestimar jugadores con 1-2 buenos partidos)
    porcentaje_historico = jugador['porcentaje_titularidades'] or 0
    if porcentaje_historico < 30:
        recomendacion = min(recomendacion, 70)

    return round(max(0, min(100, recomendacion)))


# =====================================================
# PROCESO PRINCIPAL
# =====================================================
if __name__ == "__main__":
    print("=" * 60)
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
        recomendacion = calcular_recomendacion_fantasy(player_id)

        # Actualizar en BD
        cursor.execute("""
            UPDATE jugadores_laliga
            SET recomendacion_fantasy = %s
            WHERE id = %s AND season = %s
        """, (recomendacion, player_id, SEASON))

        # Contadores
        if jugador['baja'] == 1:
            lesionados += 1
            status = "🚑 LESIONADO"
        elif recomendacion == 0:
            sin_datos += 1
            status = "❓ SIN DATOS"
        elif recomendacion >= 90:
            status = "🥇 ORO"
        elif recomendacion >= 70:
            status = "🥈 PLATA"
        elif recomendacion >= 40:
            status = "🥉 BRONCE"
        else:
            status = "🔴 RIESGO"

        actualizados += 1

        # Mostrar progreso cada 50 jugadores
        if i % 50 == 0:
            print(f"Procesados {i}/{total} jugadores...")

    # Commit final
    conn.commit()

    print("\n" + "=" * 60)
    print("✅ PROCESO COMPLETADO")
    print("=" * 60)
    print(f"Total procesados: {actualizados}")
    print(f"Lesionados (0%): {lesionados}")
    print(f"Sin datos suficientes: {sin_datos}")
    print(f"Con recomendación: {actualizados - lesionados - sin_datos}")
    print("=" * 60)

    # Estadísticas de distribución (ANTES de cerrar la conexión)
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

    print("\n📊 DISTRIBUCIÓN DE MEDALLAS:")
    print(f"  🥇 Oro (90-100%): {stats['oro']} jugadores")
    print(f"  🥈 Plata (70-89%): {stats['plata']} jugadores")
    print(f"  🥉 Bronce (40-69%): {stats['bronce']} jugadores")
    print(f"  🔴 Riesgo (0-39%): {stats['riesgo']} jugadores")
    print("=" * 60)

    # Cerrar conexión al final
    conn.close()
