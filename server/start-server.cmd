@echo off
setlocal
cd /d "%~dp0"

where node.exe >nul 2>nul
if errorlevel 1 (
    echo Node.js was not found on PATH. Install Node.js or add node.exe to PATH.
    exit /b 1
)

node.exe server.js
exit /b %errorlevel%
