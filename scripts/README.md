# LabelMe Fantasy Scripts

## Overview

This directory contains Python scripts to update the LaLiga Fantasy player database with data from API Football.

## Main Script

### `update_players.py`

**This is the only script you need to run.** It performs all necessary operations:

1. Fetches updated data for all players from API Football
2. Translates injury types and reasons to Spanish
3. Calculates fantasy recommendations (v2 algorithm with 4 components)
4. Updates the MySQL database
5. Generates detailed statistics and logs

**How to run:**

```bash
python update_players.py
```

Or use the batch file (recommended):

```bash
ejecutar_update_players.bat
```

## Automation

### Task Scheduler (Windows)

The script is configured to run automatically via Windows Task Scheduler.

**Current configuration:**
- **File:** `ejecutar_update_players.bat`
- **Frequency:** Daily (configured in Task Scheduler)
- **Log:** Results are saved in `ejecucion.log`

To view or modify the scheduled task:
1. Open Windows "Task Scheduler"
2. Find the LabelMe Fantasy related task
3. Modify frequency or settings as needed

## Fantasy Recommendation Algorithm

The script automatically calculates a recommendation (0-100%) for each player based on:

### Algorithm Components (v2)

| Component | Weight | Description |
|-----------|--------|-------------|
| **Recent Trend** | 40% | Last 5 matches with exponential weights (more recent = more weight) |
| **Consistency** | 25% | Performance stability (rating standard deviation) |
| **Performance Quality** | 25% | Average rating contextualized by position |
| **Physical Condition** | 10% | Fatigue/discomfort detection based on minutes played |

### Medal Classification

- **Gold (90-100%)**: Maximum recommendation
- **Silver (70-89%)**: Good recommendation
- **Bronze (40-69%)**: Medium recommendation
- **Risk (0-39%)**: Low recommendation

## Other Files

### `calculate_recommendation.py` - DEPRECATED

This file is NO longer needed. Its functionality has been integrated into `update_players.py`.

Kept only for historical reference.

### `update_fixtures.py`

Script to update player fixtures (matches). Run independently if needed.

**How to run:**
```bash
python update_fixtures.py
```

Or use the batch file:
```bash
ejecutar_update_fixtures.bat
```

### `calculate_stats_from_fixtures.py`

Script to recalculate statistics from saved fixtures. Useful for testing or corrections.

### `add_recommendation_column.sql`

SQL script to add the fantasy recommendation column to the players table. Only needed during initial setup.

## Requirements

### Python Libraries

```bash
pip install pymysql requests python-dotenv
```

### Environment Variables (.env)

Create a `.env` file in the project directory with:

```env
FOOTBALL_API_KEY=your_api_key
DB_HOST=localhost
DB_USER=your_user
DB_PASSWORD=your_password
DB_NAME=database_name
```

## Logs

Each execution generates a log in `ejecucion.log` with:
- Start/end date and time
- Number of players processed
- Calculated medal distribution
- Errors if any

## Troubleshooting

### Database connection error
Check credentials in the `.env` file

### API error
Verify your API key is valid at [api-football.com](https://www.api-football.com/)

### Script doesn't run automatically
1. Check Task Scheduler
2. Verify paths in the .bat file are correct
3. Make sure the Conda environment is activated correctly

## Support

For issues or suggestions, contact the LabelMe Fantasy team.

---

**Last updated:** 2026-02-23
**Algorithm version:** v2 (4 components)
**Season:** 2025
