# Scripts de LabelMe Fantasy

## 📋 Descripción General

Este directorio contiene los scripts de Python para actualizar la base de datos de jugadores de LaLiga Fantasy con datos de la API Football.

## 🚀 Script Principal

### `update_players.py`

**Este es el único script que necesitas ejecutar.** Realiza todas las operaciones necesarias:

1. ✅ Obtiene datos actualizados de todos los jugadores desde API Football
2. ✅ Traduce tipos de baja y razones al español
3. ✅ Calcula las recomendaciones fantasy (algoritmo v2 con 4 componentes)
4. ✅ Actualiza la base de datos MySQL
5. ✅ Genera estadísticas y logs detallados

**Cómo ejecutar:**

```bash
python update_players.py
```

O usa el archivo batch (recomendado):

```bash
ejecutar_update_players.bat
```

## ⚙️ Automatización

### Task Scheduler (Windows)

El script está configurado para ejecutarse automáticamente mediante Windows Task Scheduler.

**Configuración actual:**
- **Archivo:** `ejecutar_update_players.bat`
- **Frecuencia:** Diaria (configurado en Task Scheduler)
- **Log:** Los resultados se guardan en `ejecucion.log`

Para ver o modificar la tarea programada:
1. Abre "Programador de tareas" de Windows
2. Busca la tarea relacionada con LabelMe Fantasy
3. Modifica frecuencia o configuración según necesites

## 📊 Algoritmo de Recomendación Fantasy

El script calcula automáticamente una recomendación (0-100%) para cada jugador basándose en:

### Componentes del Algoritmo (v2)

| Componente | Peso | Descripción |
|------------|------|-------------|
| **Tendencia Reciente** | 40% | Últimos 5 partidos con pesos exponenciales (más reciente = más peso) |
| **Consistencia** | 25% | Estabilidad en el rendimiento (desviación estándar del rating) |
| **Calidad Rendimiento** | 25% | Rating promedio contextualizado por posición |
| **Estado Físico** | 10% | Detección de molestias/fatiga basada en minutos jugados |

### Clasificación de Medallas

- 🥇 **Oro (90-100%)**: Recomendación máxima
- 🥈 **Plata (70-89%)**: Buena recomendación
- 🥉 **Bronce (40-69%)**: Recomendación media
- 🔴 **Riesgo (0-39%)**: Recomendación baja

## 📁 Otros Archivos

### `calculate_recommendation.py` ⚠️ OBSOLETO

Este archivo ya NO es necesario. Su funcionalidad ha sido integrada en `update_players.py`.

Se mantiene solo como referencia histórica.

### `update_fixtures.py`

Script para actualizar los fixtures (partidos) de los jugadores. Se ejecuta independientemente si es necesario.

**Cómo ejecutar:**
```bash
python update_fixtures.py
```

O usa el archivo batch:
```bash
ejecutar_update_fixtures.bat
```

### `calculate_stats_from_fixtures.py`

Script para recalcular estadísticas a partir de los fixtures guardados. Útil para pruebas o correcciones.

### `add_recommendation_column.sql`

Script SQL para agregar la columna de recomendación fantasy a la tabla de jugadores. Solo necesario en la configuración inicial.

## 🔧 Requisitos

### Librerías Python

```bash
pip install pymysql requests python-dotenv
```

### Variables de Entorno (.env)

Crea un archivo `.env` en el directorio del proyecto con:

```env
FOOTBALL_API_KEY=tu_api_key
DB_HOST=localhost
DB_USER=tu_usuario
DB_PASSWORD=tu_contraseña
DB_NAME=nombre_base_datos
```

## 📝 Logs

Cada ejecución genera un log en `ejecucion.log` con:
- Fecha y hora de inicio/fin
- Número de jugadores procesados
- Distribución de medallas calculadas
- Errores si los hubiera

## 🆘 Solución de Problemas

### Error de conexión a BD
Verifica las credenciales en el archivo `.env`

### Error de API
Verifica que tu API key sea válida en [api-football.com](https://www.api-football.com/)

### Script no se ejecuta automáticamente
1. Verifica Task Scheduler
2. Revisa que las rutas en el .bat sean correctas
3. Comprueba que el entorno Conda esté activado correctamente

## 📧 Soporte

Para problemas o sugerencias, contacta al equipo de LabelMe Fantasy.

---

**Última actualización:** 2026-02-23
**Versión del algoritmo:** v2 (4 componentes)
**Temporada:** 2025
