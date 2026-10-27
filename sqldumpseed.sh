#!/bin/bash
if [ -n "$1" ]; then
    export SQL_DUMP_PATH="$1"
    echo "[INFO] Target SQL file: $1"
fi

php artisan db:seed --class=SqlDumpSeeder
