@echo off
setlocal enabledelayedexpansion

REM Require commit message
if "%~1"=="" (
    echo [ERROR] Please provide a commit message.
    echo Usage: huigit "your message"
    exit /b 1
)
Rem "by sukanta hui"
set MSG=%~1

REM Check if there are changes
git diff --quiet
if %errorlevel%==0 (
    git diff --cached --quiet
    if %errorlevel%==0 (
        echo [WARN] Nothing to commit.
        exit /b 0
    )
)

echo [INFO] Adding files...
git add .

echo [INFO] Committing...
git commit -m "%MSG%"
if %errorlevel% neq 0 exit /b 1

echo [INFO] Pulling latest (rebase)...
git pull --rebase
if %errorlevel% neq 0 exit /b 1

echo [INFO] Pushing...
git push
if %errorlevel% neq 0 exit /b 1

echo [OK] Done!
