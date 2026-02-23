#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
calculate_stats_from_fixtures.py
=================================
Script opcional que calcula estadísticas totales de jugadores
agregando datos desde la tabla jugadores_fixtures_laliga.

Útil para:
- Validar que los datos de update_players.py coinciden
- Eventualmente migrar completamente a sistema basado en fixtures
- Generar reportes personalizados
"""
#%%
import pymysql
from datetime import datetime
import os
from dotenv import load_dotenv

load_dotenv()

# === CONEXIÓN A LA BBDD ===
def get_db_connection():
    return pymysql.connect(
        host=os.getenv('DB_HOST'),
        user=os.getenv('DB_USER'),
        password=os.getenv('DB_PASSWORD'),
        database=os.getenv('DB_NAME'),
        charset="utf8mb4"
    )

def calculate_aggregated_stats(season=2025):
    """
    Calcula estadísticas totales por jugador desde jugadores_fixtures_laliga
    """
    conn = get_db_connection()
    cursor = conn.cursor(pymysql.cursors.DictCursor)
    
    print("=" * 60)
    print("📊 CÁLCULO DE ESTADÍSTICAS AGREGADAS DESDE FIXTURES")
    print(f"   Temporada {season}")
    print("=" * 60)
    
    # Consulta SQL que agrega todas las estadísticas
    sql = """
    SELECT 
        player_id,
        team_id,
        team_name,
        position,
        
        -- Partidos y minutos
        COUNT(DISTINCT fixture_id) as partidos_jugados,
        SUM(minutes) as minutos_totales,
        SUM(CASE WHEN substitute = 0 AND minutes > 0 THEN 1 ELSE 0 END) as veces_titular,
        SUM(CASE WHEN substitute = 1 THEN 1 ELSE 0 END) as veces_suplente,
        
        -- Rating promedio (solo partidos donde jugó)
        ROUND(AVG(CASE WHEN minutes > 0 THEN rating ELSE NULL END), 2) as rating_promedio,
        
        -- Goles y asistencias
        SUM(goals) as goles_totales,
        SUM(assists) as asistencias_totales,
        SUM(goals_conceded) as goles_concedidos,
        SUM(saves) as salvadas_totales,
        
        -- Disparos
        SUM(shots_total) as disparos_totales,
        SUM(shots_on) as disparos_puerta,
        
        -- Pases
        SUM(passes_total) as pases_totales,
        SUM(passes_key) as pases_clave,
        ROUND(AVG(CASE WHEN passes_total > 0 THEN passes_accuracy ELSE NULL END), 1) as precision_pases_promedio,
        
        -- Defensivas
        SUM(tackles_total) as entradas_totales,
        SUM(blocks) as bloqueos_totales,
        SUM(interceptions) as intercepciones_totales,
        
        -- Duelos
        SUM(duels_total) as duelos_totales,
        SUM(duels_won) as duelos_ganados,
        
        -- Regates
        SUM(dribbles_attempts) as regates_intentados,
        SUM(dribbles_success) as regates_exitosos,
        
        -- Faltas y tarjetas
        SUM(fouls_drawn) as faltas_recibidas,
        SUM(fouls_committed) as faltas_cometidas,
        SUM(yellow_cards) as amarillas_totales,
        SUM(red_cards) as rojas_totales,
        
        -- Penales
        SUM(penalty_scored) as penales_anotados,
        SUM(penalty_missed) as penales_fallados,
        SUM(penalty_saved) as penales_atajados,
        
        -- Otros
        SUM(offsides) as fueras_juego_totales,
        
        -- Metadatos
        MAX(fixture_date) as ultimo_partido,
        season
        
    FROM jugadores_fixtures_laliga
    WHERE season = %s
    GROUP BY player_id, team_id, team_name, position, season
    ORDER BY team_name, rating_promedio DESC
    """
    
    cursor.execute(sql, (season,))
    results = cursor.fetchall()
    
    print(f"\n✅ {len(results)} jugadores procesados\n")
    
    # Mostrar ejemplo de los primeros 5 jugadores
    print("📋 MUESTRA DE DATOS CALCULADOS (Top 5 por rating):")
    print("-" * 60)
    
    for i, player in enumerate(results[:5], 1):
        print(f"\n{i}. Player ID: {player['player_id']}")
        print(f"   Equipo: {player['team_name']}")
        print(f"   Posición: {player['position']}")
        print(f"   Partidos: {player['partidos_jugados']} | Titular: {player['veces_titular']} | Suplente: {player['veces_suplente']}")
        print(f"   Minutos: {player['minutos_totales']} | Rating: {player['rating_promedio']}")
        print(f"   Goles: {player['goles_totales']} | Asistencias: {player['asistencias_totales']}")
        print(f"   Amarillas: {player['amarillas_totales']} | Rojas: {player['rojas_totales']}")
    
    cursor.close()
    conn.close()
    
    print("\n" + "=" * 60)
    print(f"✅ Cálculo completado - {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print("=" * 60)
    
    return results

def compare_with_current_table(player_id):
    """
    Compara las estadísticas de un jugador específico entre:
    - Tabla jugadores_laliga (datos de update_players.py)
    - Datos calculados desde jugadores_fixtures_laliga
    """
    conn = get_db_connection()
    cursor = conn.cursor(pymysql.cursors.DictCursor)
    
    print("\n" + "=" * 60)
    print(f"🔍 COMPARACIÓN DE ESTADÍSTICAS - Player ID: {player_id}")
    print("=" * 60)
    
    # Obtener datos de tabla principal
    cursor.execute("""
        SELECT nombre, goles_total, goles_asistencias, minutos, partidos, 
               rating, amarillas, rojas
        FROM jugadores_laliga 
        WHERE id = %s AND season = 2025
    """, (player_id,))
    
    current = cursor.fetchone()
    
    if not current:
        print(f"❌ Jugador {player_id} no encontrado en jugadores_laliga")
        cursor.close()
        conn.close()
        return
    
    # Calcular desde fixtures
    cursor.execute("""
        SELECT 
            COUNT(DISTINCT fixture_id) as partidos,
            SUM(minutes) as minutos,
            ROUND(AVG(CASE WHEN minutes > 0 THEN rating ELSE NULL END), 2) as rating,
            SUM(goals) as goles,
            SUM(assists) as asistencias,
            SUM(yellow_cards) as amarillas,
            SUM(red_cards) as rojas
        FROM jugadores_fixtures_laliga
        WHERE player_id = %s AND season = 2025
    """, (player_id,))
    
    calculated = cursor.fetchone()
    
    # Mostrar comparación
    print(f"\n👤 {current['nombre']}")
    print("-" * 60)
    
    stats = [
        ('Goles', current['goles_total'], calculated['goles']),
        ('Asistencias', current['goles_asistencias'], calculated['asistencias']),
        ('Minutos', current['minutos'], calculated['minutos']),
        ('Partidos', current['partidos'], calculated['partidos']),
        ('Rating', current['rating'], calculated['rating']),
        ('Amarillas', current['amarillas'], calculated['amarillas']),
        ('Rojas', current['rojas'], calculated['rojas'])
    ]
    
    print(f"{'Estadística':<15} {'Tabla Principal':<20} {'Desde Fixtures':<20} {'Match':<10}")
    print("-" * 60)
    
    all_match = True
    for stat_name, main_val, calc_val in stats:
        match = "✅" if str(main_val) == str(calc_val) else "❌"
        if match == "❌":
            all_match = False
        print(f"{stat_name:<15} {str(main_val):<20} {str(calc_val):<20} {match:<10}")
    
    print("-" * 60)
    if all_match:
        print("✅ TODAS LAS ESTADÍSTICAS COINCIDEN")
    else:
        print("⚠️  HAY DIFERENCIAS - Revisar datos")
    
    cursor.close()
    conn.close()

# === MENÚ PRINCIPAL ===
def main():
    print("\n" + "=" * 60)
    print("📊 HERRAMIENTA DE ANÁLISIS DE FIXTURES")
    print("=" * 60)
    print("\nOpciones:")
    print("1. Calcular estadísticas agregadas de todos los jugadores")
    print("2. Comparar jugador específico (by ID)")
    print("3. Salir")
    
    opcion = input("\nSelecciona una opción (1-3): ").strip()
    
    if opcion == "1":
        calculate_aggregated_stats(2025)
    
    elif opcion == "2":
        player_id = input("Ingresa el ID del jugador: ").strip()
        try:
            player_id = int(player_id)
            compare_with_current_table(player_id)
        except ValueError:
            print("❌ ID inválido")
    
    elif opcion == "3":
        print("👋 ¡Hasta luego!")
    
    else:
        print("❌ Opción no válida")

if __name__ == "__main__":
    main()
# %%
