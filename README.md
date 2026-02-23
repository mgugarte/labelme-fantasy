# LabelMe Fantasy

Sistema de gestión y análisis de datos para LaLiga Fantasy. Incluye scripts de actualización automática de jugadores y un tema de WordPress personalizado.
El resultado se encuentra en labelme.es

## Estructura del Proyecto

```
labelme-fantasy/
├── scripts/              # Scripts Python para actualización de datos
│   ├── update_players.py # Script principal de actualización
│   ├── update_fixtures.py
│   └── ...
├── wordpress/            # Tema hijo de Astra para WordPress
│   └── astra_child/
│       ├── css/          # Estilos personalizados
│       └── js/           # Scripts JavaScript
└── .env.example          # Plantilla de variables de entorno
```

## Funcionalidades

### Scripts de Actualización
- Obtención de datos de jugadores desde API Football
- Cálculo automático de recomendaciones fantasy (algoritmo v2)
- Actualización de base de datos MySQL
- Ejecución programada mediante Task Scheduler

### Tema WordPress
- Tema hijo de Astra con estilos personalizados
- Integración con la base de datos de jugadores
- Interfaz para visualización de estadísticas

## Requisitos

### Python
```bash
pip install pymysql requests python-dotenv
```

### Variables de Entorno
Copia `.env.example` a `scripts/.env` y configura:

```env
FOOTBALL_API_KEY=tu_api_key
DB_HOST=localhost
DB_USER=tu_usuario
DB_PASSWORD=tu_contraseña
DB_NAME=nombre_base_datos
```

## Uso Rápido

```bash
# Activar entorno virtual
.venv\Scripts\activate

# Ejecutar actualización de jugadores
cd scripts
python update_players.py
```

O usa el archivo batch:
```bash
scripts\ejecutar_update_players.bat
```

## Documentación Adicional

- [Scripts - Documentación detallada](scripts/README.md)

## API Utilizada

- [API-Football](https://www.api-football.com/) - Datos de LaLiga

---

**Temporada actual:** 2025
