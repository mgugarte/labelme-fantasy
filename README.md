# LabelMe Fantasy

Data management and analysis system for LaLiga Fantasy. Includes automatic player update scripts and a custom WordPress theme.

Live site: https://labelme.es/

> **Note:** Much of the code, database fields, and output are in Spanish since this project is for LaLiga (Spanish football league) and I'm Spanish.

## Project Structure

```
labelme-fantasy/
├── scripts/              # Python scripts for data updates
│   ├── update_players.py # Main update script
│   ├── update_fixtures.py
│   └── ...
├── wordpress/            # Astra child theme for WordPress
│   └── astra_child/
│       ├── css/          # Custom styles
│       └── js/           # JavaScript files
└── .env.example          # Environment variables template
```

## Features

### Update Scripts
- Fetch player data from API Football
- Automatic fantasy recommendation calculation (v2 algorithm)
- MySQL database updates
- Scheduled execution via Task Scheduler

### WordPress Theme
- Astra child theme with custom styles
- Integration with player database
- Statistics visualization interface

## Requirements

### Python
```bash
pip install pymysql requests python-dotenv
```

### Environment Variables
Copy `.env.example` to `scripts/.env` and configure:

```env
FOOTBALL_API_KEY=your_api_key
DB_HOST=localhost
DB_USER=your_user
DB_PASSWORD=your_password
DB_NAME=database_name
```

## Quick Start

```bash
# Activate virtual environment
.venv\Scripts\activate

# Run player update
cd scripts
python update_players.py
```

Or use the batch file:
```bash
scripts\ejecutar_update_players.bat
```

## Additional Documentation

- [Scripts - Detailed documentation](scripts/README.md)

## API Used

- [API-Football](https://www.api-football.com/) - LaLiga data

---

**Current season:** 2025
