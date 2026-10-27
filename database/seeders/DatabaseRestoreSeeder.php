<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * =============================================================================
 * DatabaseRestoreSeeder
 * =============================================================================
 *
 * Restores the CNAT MySQL database using an SQL backup file.
 * Automatically resolves the newest SQL dump between the canonical repository
 * copy (database/seeders/sql/cnat_db.sql) and the storage backups folder
 * (storage/app/backups/*.sql), or accepts a custom path via SQL_DUMP_PATH.
 *
 * Usage:
 *   php artisan db:seed --class=DatabaseRestoreSeeder
 *   php artisan db:restore
 *   php artisan db:restore "path/to/backup.sql"
 * =============================================================================
 */
class DatabaseRestoreSeeder extends Seeder
{
    /**
     * Default canonical SQL file relative to base path.
     */
    protected string $defaultSqlFile = 'database/seeders/sql/bharat_db.sql';

    /**
     * Backup folder relative to base path.
     */
    protected string $backupFolder = 'storage/app/backups';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = $this->resolveSqlFilePath();

        if (!$filePath || !file_exists($filePath)) {
            $errorMsg = "DatabaseRestoreSeeder: Backup SQL file not found at [{$filePath}].";
            if ($this->command) {
                $this->command->error($errorMsg);
            }
            Log::error($errorMsg);
            return;
        }

        $fileSize = filesize($filePath);
        $fileSizeHuman = $this->formatBytes($fileSize);

        if ($this->command) {
            $this->command->info("Restoring database from backup: {$filePath} ({$fileSizeHuman})");
        }

        $startTime = microtime(true);

        try {
            // Disable foreign key checks for clean table drops & inserts
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Read and execute the entire SQL dump
            $sql = file_get_contents($filePath);
            DB::unprepared($sql);

            $duration = round(microtime(true) - $startTime, 2);

            if ($this->command) {
                $this->command->info("✓ Database successfully restored in {$duration}s.");
                $this->displaySummary();
            }

            Log::info("DatabaseRestoreSeeder completed successfully.", [
                'file' => $filePath,
                'size' => $fileSizeHuman,
                'duration_seconds' => $duration,
            ]);

        } catch (\Throwable $e) {
            $duration = round(microtime(true) - $startTime, 2);
            $errorMsg = "Database restore failed after {$duration}s: " . $e->getMessage();

            if ($this->command) {
                $this->command->error($errorMsg);
            }

            Log::error($errorMsg, [
                'file' => $filePath,
                'exception' => $e,
            ]);

            throw $e;

        } finally {
            // Guarantee foreign key checks are re-enabled
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    /**
     * Resolve the SQL file to execute.
     * Checks in order:
     *   1. Explicit path in env('SQL_DUMP_PATH')
     *   2. Automatically picks the newest file between database/seeders/sql/cnat_db.sql
     *      and storage/app/backups/*.sql based on last modified time.
     */
    protected function resolveSqlFilePath(): ?string
    {
        // 1. Explicit environment variable override
        $customPath = env('SQL_DUMP_PATH');
        if ($customPath && file_exists($customPath)) {
            return $customPath;
        }

        // 2. Gather candidates from repository seeder folder and backup folder
        $candidates = [];

        $canonical = base_path($this->defaultSqlFile);
        if (file_exists($canonical)) {
            $candidates[$canonical] = filemtime($canonical);
        }

        $backupDir = base_path($this->backupFolder);
        if (is_dir($backupDir)) {
            $backupFiles = glob($backupDir . DIRECTORY_SEPARATOR . '*.sql') ?: [];
            foreach ($backupFiles as $file) {
                $candidates[$file] = filemtime($file);
            }
        }

        if (empty($candidates)) {
            return null;
        }

        // Pick the most recently modified file
        arsort($candidates);
        return array_key_first($candidates);
    }

    /**
     * Display a clean summary of restored tables and row counts in CLI.
     */
    protected function displaySummary(): void
    {
        $tables = [
            'users',
            'user_types',
            'employees',
            'departments',
            'designations',
            'students',
            'courses',
            'course_details',
            'admissions',
            'certificates',
            'subjects',
            'chapters',
            'topics',
            'questions',
            'options',
            'states',
            'districts',
            'fee_modes',
            'food_preferences',
            'genders',
        ];

        $rows = [];
        foreach ($tables as $table) {
            try {
                $count = DB::table($table)->count();
                $rows[] = [$table, number_format($count)];
            } catch (\Throwable) {
                // Table might not exist or empty
            }
        }

        if (!empty($rows) && $this->command) {
            $this->command->newLine();
            $this->command->table(['Table', 'Total Records'], $rows);
        }
    }

    /**
     * Format bytes to human readable format.
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }
}
