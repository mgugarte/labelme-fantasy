@echo off
REM Batch file para ejecutar update_market_values.py diariamente
chcp 65001 >nul

echo ============================================ >> "%~dp0ejecucion.log"
echo Ejecutando script: %date% %time% >> "%~dp0ejecucion.log"

cd /d "%~dp0"

call "C:\Users\migue\anaconda3\Scripts\activate.bat"
call conda activate my_fantasy_env

python update_market_values.py >> "%~dp0ejecucion.log" 2>&1

call conda deactivate

echo Script finalizado: %date% %time% >> "%~dp0ejecucion.log"
echo ============================================ >> "%~dp0ejecucion.log"
echo.
