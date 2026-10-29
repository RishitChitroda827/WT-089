@echo off
title StudentHub Portal Server - CHARUSAT
echo ========================================================
echo Starting StudentHub Portal - Web Development Frameworks
echo ========================================================
echo.
echo Make sure MySQL is running in your XAMPP Control Panel!
echo.

:: Detect PHP executable (PATH or XAMPP)
set "PHP_BIN=php"
where php >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    if exist "C:\xampp\php\php.exe" (
        set "PHP_BIN=C:\xampp\php\php.exe"
    ) else (
        echo [ERROR] PHP was not found in PATH or at C:\xampp\php\php.exe.
        echo Please ensure XAMPP is installed.
        pause
        exit /b 1
    )
)

echo Starting PHP built-in server at http://localhost:8000 ...
echo Press Ctrl + C anytime to stop the server.
echo.
start "" http://localhost:8000/index.php
"%PHP_BIN%" -S localhost:8000 -t "%~dp0"
pause
