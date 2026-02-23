@echo off
REM ============================================================
REM LABELME FANTASY - Script de Actualización Completo
REM ============================================================
REM Este script ejecuta:
REM 1. Actualización de jugadores desde API Football
REM 2. Traducción de tipos de baja al español
REM 3. Cálculo de recomendaciones fantasy (algoritmo v2)
REM ============================================================

REM Configurar la codificación UTF-8
chcp 65001 >nul

echo.
echo ============================================================
echo LABELME FANTASY - Actualización Completa
echo ============================================================
echo Inicio: %date% %time%
echo.

REM Registrar en log
echo ============================================ >> "%~dp0ejecucion.log"
echo Ejecutando script: %date% %time% >> "%~dp0ejecucion.log"

REM Cambiar al directorio donde está el script
cd /d "%~dp0"

REM Inicializar Conda para el batch
echo [1/3] Inicializando entorno Python...
echo Inicializando Conda... >> "%~dp0ejecucion.log"
call "C:\Users\migue\anaconda3\Scripts\activate.bat"

REM Activar el entorno virtual de Conda
echo Activando entorno my_fantasy_env... >> "%~dp0ejecucion.log"
call conda activate my_fantasy_env

echo [2/3] Actualizando jugadores y calculando recomendaciones...
echo.

REM Ejecutar el script Python (ahora incluye recomendaciones)
python update_players.py >> "%~dp0ejecucion.log" 2>&1

REM Desactivar el entorno
call conda deactivate

REM Registrar finalización
echo.
echo [3/3] Proceso finalizado
echo Script finalizado: %date% %time% >> "%~dp0ejecucion.log"
echo ============================================ >> "%~dp0ejecucion.log"
echo.

echo ============================================================
echo Finalizado: %date% %time%
echo ============================================================
echo.
echo Para ver detalles del proceso, revisa: ejecucion.log
echo.

REM Si quieres que la ventana se quede abierta para ver errores, descomenta la siguiente línea
REM pause
