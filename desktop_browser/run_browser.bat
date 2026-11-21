@echo off
setlocal
cd /d "%~dp0"
py -3.15 browser_app.py
if errorlevel 1 (
    echo.
    echo The browser could not start. Check the Python and PySide6 installation.
    pause
)
