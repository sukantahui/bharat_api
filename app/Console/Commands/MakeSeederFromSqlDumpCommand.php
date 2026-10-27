<?php

namespace App\Console\Commands;

use App\Services\SqlToSeederConverter;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeSeederFromSqlDumpCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:seeder-from-sql
                            {sql_file : Path to the .sql dump file}
                            {--class= : Name of the Seeder class to generate}
                            {--table= : Filter and generate seeder for a specific table name only}
                            {--mode=insert : Output mode: "insert" (DB::table array chunks) or "raw" (DB::unprepared raw SQL)}
                            {--chunk=500 : Number of records per insert chunk (in "insert" mode)}
                            {--truncate : Truncate the table before inserting records}
                            {--disable-fk : Disable foreign key constraints during seeding}
                            {--output= : Custom output file path (defaults to database/seeders/<Class>.php)}
                            {--force : Overwrite existing seeder file if it already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a Laravel Seeder file from a MySQL/SQL dump file (.sql)';

    /**
     * Execute the console command.
     */
    public function handle(SqlToSeederConverter $converter): int
    {
        $sqlFilePath = $this->argument('sql_file');

        // Resolve absolute or relative path
        if (!file_exists($sqlFilePath)) {
            $candidatePath = base_path($sqlFilePath);
            if (file_exists($candidatePath)) {
                $sqlFilePath = $candidatePath;
            } else {
                $this->error("SQL dump file not found at: {$sqlFilePath}");
                return self::FAILURE;
            }
        }

        $className   = $this->option('class');
        $tableName   = $this->option('table');
        $mode        = strtolower($this->option('mode') ?: 'insert');
        $chunkSize   = (int)($this->option('chunk') ?: 500);
        $truncate    = (bool)$this->option('truncate');
        $disableFk   = (bool)$this->option('disable-fk');
        $outputPath  = $this->option('output');
        $force       = (bool)$this->option('force');

        if (!in_array($mode, ['insert', 'raw'], true)) {
            $this->error("Invalid mode '{$mode}'. Allowed modes are: 'insert', 'raw'.");
            return self::FAILURE;
        }

        $this->info("Parsing SQL dump file: {$sqlFilePath}...");

        try {
            $sqlContent = file_get_contents($sqlFilePath);
            $tablesData = $converter->parseSql($sqlContent, $tableName);

            if (empty($tablesData)) {
                $this->error("No valid INSERT / REPLACE statements found in {$sqlFilePath}" . ($tableName ? " for table '{$tableName}'." : "."));
                return self::FAILURE;
            }

            // Determine Seeder class name if not provided
            if (empty($className)) {
                if ($tableName) {
                    $className = Str::studly(Str::singular($tableName)) . 'Seeder';
                } elseif (count($tablesData) === 1) {
                    $firstTable = array_key_first($tablesData);
                    $className = Str::studly(Str::singular($firstTable)) . 'Seeder';
                } else {
                    $baseName = pathinfo($sqlFilePath, PATHINFO_FILENAME);
                    $cleanName = preg_replace('/[^a-zA-Z0-9]+/', '_', $baseName);
                    $className = Str::studly($cleanName) . 'Seeder';
                }
            } else {
                $className = Str::studly($className);
                if (!Str::endsWith($className, 'Seeder')) {
                    $className .= 'Seeder';
                }
            }

            // Determine output path
            if (empty($outputPath)) {
                $outputPath = database_path("seeders/{$className}.php");
            }

            // Check if file exists and handle overwrite
            if (file_exists($outputPath) && !$force) {
                if (!$this->confirm("File [{$outputPath}] already exists. Do you wish to overwrite it?")) {
                    $this->warn('Seeder generation aborted.');
                    return self::SUCCESS;
                }
            }

            $options = [
                'class'      => $className,
                'table'      => $tableName,
                'mode'       => $mode,
                'chunk'      => $chunkSize,
                'truncate'   => $truncate,
                'disable_fk' => $disableFk,
                'raw_sql'    => $sqlContent,
            ];

            $result = $converter->convertFile($sqlFilePath, $outputPath, $options);

            $this->newLine();
            $this->info("✓ Seeder generated successfully!");
            $this->line("  <comment>Class:</comment>   {$result['class_name']}");
            $this->line("  <comment>File:</comment>    {$result['file_path']}");
            $this->line("  <comment>Tables:</comment>  {$result['total_tables']} (" . implode(', ', array_keys($tablesData)) . ")");
            $this->line("  <comment>Rows:</comment>    {$result['total_rows']}");
            $this->line("  <comment>Mode:</comment>    {$mode}");
            if ($mode === 'insert') {
                $this->line("  <comment>Chunk:</comment>   {$chunkSize} rows per insert");
            }
            $this->newLine();
            $this->line("You can run this seeder with:");
            $this->line("  <info>php artisan db:seed --class={$result['class_name']}</info>");
            $this->newLine();

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Failed to generate seeder: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
