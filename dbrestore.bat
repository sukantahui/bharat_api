@echo off
setlocal enabledelayedexpansion

title CNAT API - Database Restore Tool

echo ===============================================================================
echo                CNAT API - MySQL Database Restore Utility
echo ===============================================================================
echo.

REM Ensure working directory is the project root where this batch file lives
cd /d "%~dp0"

set "BACKUP_DIR=storage\app\backups"
set "FALLBACK_FILE=database\seeders\sql\cnat_db.sql"
set "RESTORE_FILE="

REM 1. Check if user passed an explicit file as argument
if not "%~1"=="" (
    if exist "%~1" (
        set "RESTORE_FILE=%~1"
        echo [INFO] Using custom backup file provided as argument.
    ) else if exist "%BACKUP_DIR%\%~1" (
        set "RESTORE_FILE=%BACKUP_DIR%\%~1"
        echo [INFO] Found specified backup in %BACKUP_DIR%.
    ) else (
        echo [ERROR] The specified backup file was not found: "%~1"
        echo.
        goto :error_exit
    )
)

REM 2. If no argument provided, find the newest .sql file in storage\app\backups
if "!RESTORE_FILE!"=="" (
    if exist "%BACKUP_DIR%\*.sql" (
        for /f "delims=" %%F in ('dir "%BACKUP_DIR%\*.sql" /b /o:d /a:-d 2^>nul') do (
            set "LATEST_BACKUP=%%F"
        )
        if not "!LATEST_BACKUP!"=="" (
            set "RESTORE_FILE=%BACKUP_DIR%\!LATEST_BACKUP!"
            echo [INFO] Automatically selected the newest backup from %BACKUP_DIR%.
        )
    )
)

REM 3. Fallback to repository seed file if no backups exist in storage\app\backups
if "!RESTORE_FILE!"=="" (
    if exist "%FALLBACK_FILE%" (
        set "RESTORE_FILE=%FALLBACK_FILE%"
        echo [INFO] No backups found in %BACKUP_DIR%. Falling back to %FALLBACK_FILE%.
    ) else (
        echo [ERROR] No SQL backup files found in "%BACKUP_DIR%" or "%FALLBACK_FILE%".
        echo [TIP]   You can generate a new backup first using: php artisan db:backup
        echo.
        goto :error_exit
    )
)

REM 4. Display details of the chosen file
for %%I in ("!RESTORE_FILE!") do (
    set "FILE_SIZE=%%~zI"
    set "FILE_DATE=%%~tI"
    set "FULL_PATH=%%~fI"
)

echo.
echo -------------------------------------------------------------------------------
echo   Selected Backup File Details:
echo -------------------------------------------------------------------------------
echo   File Name     : !RESTORE_FILE!
echo   Full Path     : !FULL_PATH!
echo   Last Modified : !FILE_DATE!
echo   Size in Bytes : !FILE_SIZE! bytes
echo -------------------------------------------------------------------------------
echo.

echo [INFO] Starting database restore via Laravel Artisan...
echo.

php artisan db:restore "!RESTORE_FILE!"

if %errorlevel% neq 0 (
    echo.
    echo ===============================================================================
    echo   [ERROR] Database restore failed with exit code %errorlevel%!
    echo ===============================================================================
    goto :error_exit
)

echo.
echo ===============================================================================
echo   [SUCCESS] Database restore finished successfully!
echo ===============================================================================
echo.
pause
exit /b 0

:error_exit
echo.
pause
exit /b 1
