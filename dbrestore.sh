#!/bin/bash

# CNAT API - Database Restore Script (Bash / WSL / Linux / macOS)
# Automatically selects the newest backup from storage/app/backups/

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR" || exit 1

BACKUP_DIR="storage/app/backups"
FALLBACK_FILE="database/seeders/sql/cnat_db.sql"
RESTORE_FILE=""

echo "==============================================================================="
echo "               CNAT API - MySQL Database Restore Utility"
echo "==============================================================================="
echo ""

# 1. Custom file passed as argument
if [ -n "$1" ]; then
    if [ -f "$1" ]; then
        RESTORE_FILE="$1"
    elif [ -f "$BACKUP_DIR/$1" ]; then
        RESTORE_FILE="$BACKUP_DIR/$1"
    else
        echo "[ERROR] Specified backup file not found: $1"
        exit 1
    fi
fi

# 2. Pick newest file from storage/app/backups
if [ -z "$RESTORE_FILE" ]; then
    if [ -d "$BACKUP_DIR" ]; then
        LATEST_BACKUP=$(ls -t "$BACKUP_DIR"/*.sql 2>/dev/null | head -n 1)
        if [ -n "$LATEST_BACKUP" ] && [ -f "$LATEST_BACKUP" ]; then
            RESTORE_FILE="$LATEST_BACKUP"
            echo "[INFO] Automatically selected the newest backup from $BACKUP_DIR."
        fi
    fi
fi

# 3. Fallback
if [ -z "$RESTORE_FILE" ]; then
    if [ -f "$FALLBACK_FILE" ]; then
        RESTORE_FILE="$FALLBACK_FILE"
        echo "[INFO] No backups found in $BACKUP_DIR. Using fallback $FALLBACK_FILE."
    else
        echo "[ERROR] No SQL backup files found."
        exit 1
    fi
fi

echo "  File: $RESTORE_FILE"
echo ""

php artisan db:restore "$RESTORE_FILE"
