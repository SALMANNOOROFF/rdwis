@echo off
title RDWIS Smart Startup
color 0A

:: Wait 10 seconds after Windows login
timeout /t 10 /nobreak >nul

set "PROJECT=H:\RDWIS 2.0\RDWIS APP 2.0"
set "IDE=C:\Users\RDW\AppData\Local\Programs\Antigravity IDE\Antigravity IDE.exe"
set "DBEAVER=C:\Program Files\DBeaver\dbeaver.exe"
set "CHROME=C:\Program Files\Google\Chrome\Application\chrome.exe"

echo ==========================================
echo      RDWIS SMART STARTUP
echo ==========================================

:: ------------------------------------------------
:: Antigravity IDE
:: ------------------------------------------------
tasklist | find /I "Antigravity IDE.exe" >nul
if errorlevel 1 (
    echo Starting Antigravity IDE...
    start "" "%IDE%" "%PROJECT%"
) else (
    echo Antigravity IDE already running.
)

timeout /t 2 >nul

:: ------------------------------------------------
:: DBeaver
:: ------------------------------------------------
tasklist | find /I "dbeaver.exe" >nul
if errorlevel 1 (
    echo Starting DBeaver...
    start "" "%DBEAVER%"
) else (
    echo DBeaver already running.
)

timeout /t 2 >nul

:: ------------------------------------------------
:: Python Server (Port 8000)
:: ------------------------------------------------
netstat -ano | find ":8000" | find "LISTENING" >nul

if errorlevel 1 (
    echo Starting Python Server...
    start "RDWIS Server" cmd /k "cd /d "%PROJECT%" && (python start_server.py || py start_server.py)"
) else (
    echo Python Server already running on port 8000.
)

timeout /t 4 >nul

:: ------------------------------------------------
:: Chrome Profile 1
:: ------------------------------------------------
start "" "%CHROME%" --profile-directory="Profile 1" https://chatgpt.com
start "" "%CHROME%" --profile-directory="Profile 1" https://gemini.google.com
start "" "%CHROME%" --profile-directory="Profile 1" https://claude.ai

timeout /t 2 >nul

:: ------------------------------------------------
:: Chrome Default Profile
:: ------------------------------------------------
start "" "%CHROME%" --profile-directory="Default" https://chatgpt.com
start "" "%CHROME%" --profile-directory="Default" https://claude.ai
start "" "%CHROME%" --profile-directory="Default" https://web.whatsapp.com

echo.
echo RDWIS Startup Completed.
exit