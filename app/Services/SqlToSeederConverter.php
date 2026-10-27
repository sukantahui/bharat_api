<?php

namespace App\Services;

use Illuminate\Support\Str;

class SqlToSeederConverter
{
    /**
     * Parse SQL content into structured table data.
     *
     * @param string $sqlContent
     * @param string|null $tableFilter Optional table name to filter
     * @return array<string, array{columns: ?array, rows: array}>
     */
    public function parseSql(string $sqlContent, ?string $tableFilter = null): array
    {
        $tables = [];

        // Normalize line endings
        $sqlContent = str_replace(["\r\n", "\r"], "\n", $sqlContent);

        // Extract INSERT / REPLACE statements
        $statements = $this->extractInsertStatements($sqlContent);

        foreach ($statements as $stmt) {
            $parsed = $this->parseInsertStatement($stmt);
            if (!$parsed) {
                continue;
            }

            $tableName = $parsed['table'];

            // If table filter is specified, skip non-matching tables
            if ($tableFilter && strcasecmp($tableName, $tableFilter) !== 0) {
                continue;
            }

            if (!isset($tables[$tableName])) {
                $tables[$tableName] = [
                    'columns' => $parsed['columns'],
                    'rows'    => [],
                ];
            } elseif ($tables[$tableName]['columns'] === null && $parsed['columns'] !== null) {
                $tables[$tableName]['columns'] = $parsed['columns'];
            }

            foreach ($parsed['rows'] as $row) {
                $tables[$tableName]['rows'][] = $row;
            }
        }

        return $tables;
    }

    /**
     * Generate Seeder PHP class code from parsed table data.
     *
     * @param string $className
     * @param array<string, array{columns: ?array, rows: array}> $tablesData
     * @param array $options
     * @return string
     */
    public function generateSeederCode(string $className, array $tablesData, array $options = []): string
    {
        $mode        = $options['mode'] ?? 'insert'; // 'insert' or 'raw'
        $chunkSize   = (int)($options['chunk'] ?? 500);
        $truncate    = !empty($options['truncate']);
        $disableFk   = !empty($options['disable_fk']);
        $rawSql      = $options['raw_sql'] ?? '';

        if ($chunkSize <= 0) {
            $chunkSize = 500;
        }

        $className = Str::studly($className);
        if (!Str::endsWith($className, 'Seeder')) {
            $className .= 'Seeder';
        }

        $lines = [];
        $lines[] = "<?php";
        $lines[] = "";
        $lines[] = "namespace Database\Seeders;";
        $lines[] = "";
        $lines[] = "use Illuminate\Database\Seeder;";
        $lines[] = "use Illuminate\Support\Facades\DB;";
        $lines[] = "use Illuminate\Support\Facades\Schema;";
        $lines[] = "";
        $lines[] = "class {$className} extends Seeder";
        $lines[] = "{";
        $lines[] = "    /**";
        $lines[] = "     * Run the database seeds.";
        $lines[] = "     */";
        $lines[] = "    public function run(): void";
        $lines[] = "    {";

        if ($disableFk) {
            $lines[] = "        Schema::disableForeignKeyConstraints();";
            $lines[] = "";
        }

        if ($mode === 'raw') {
            $lines[] = $this->generateRawSqlRunMethod($rawSql, $tablesData, $truncate);
        } else {
            $lines[] = $this->generateArrayInsertRunMethod($tablesData, $chunkSize, $truncate);
        }

        if ($disableFk) {
            $lines[] = "";
            $lines[] = "        Schema::enableForeignKeyConstraints();";
        }

        $lines[] = "    }";
        $lines[] = "}";
        $lines[] = "";

        return implode("\n", $lines);
    }

    /**
     * Convert an input SQL file and write the generated Seeder to disk.
     *
     * @param string $inputSqlPath
     * @param string|null $outputFilePath
     * @param array $options
     * @return array{file_path: string, class_name: string, total_tables: int, total_rows: int}
     */
    public function convertFile(string $inputSqlPath, ?string $outputFilePath = null, array $options = []): array
    {
        if (!file_exists($inputSqlPath) || !is_readable($inputSqlPath)) {
            throw new \InvalidArgumentException("SQL file not found or not readable: {$inputSqlPath}");
        }

        $sqlContent = file_get_contents($inputSqlPath);
        $tableFilter = $options['table'] ?? null;
        $tablesData = $this->parseSql($sqlContent, $tableFilter);

        if (empty($tablesData)) {
            throw new \RuntimeException("No INSERT statements found in the SQL file" . ($tableFilter ? " for table '{$tableFilter}'." : "."));
        }

        // Determine class name
        $className = $options['class'] ?? null;
        if (empty($className)) {
            if ($tableFilter) {
                $className = Str::studly(Str::singular($tableFilter)) . 'Seeder';
            } elseif (count($tablesData) === 1) {
                $firstTable = array_key_first($tablesData);
                $className = Str::studly(Str::singular($firstTable)) . 'Seeder';
            } else {
                $baseName = pathinfo($inputSqlPath, PATHINFO_FILENAME);
                $cleanName = preg_replace('/[^a-zA-Z0-9]+/', '_', $baseName);
                $className = Str::studly($cleanName) . 'Seeder';
            }
        }

        $options['raw_sql'] = $sqlContent;
        $code = $this->generateSeederCode($className, $tablesData, $options);

        // Determine output file path
        if (empty($outputFilePath)) {
            $cleanClassName = Str::studly($className);
            if (!Str::endsWith($cleanClassName, 'Seeder')) {
                $cleanClassName .= 'Seeder';
            }
            $outputFilePath = database_path("seeders/{$cleanClassName}.php");
        }

        $dir = dirname($outputFilePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($outputFilePath, $code);

        $totalRows = 0;
        foreach ($tablesData as $tData) {
            $totalRows += count($tData['rows']);
        }

        return [
            'file_path'    => $outputFilePath,
            'class_name'   => Str::studly($className),
            'total_tables' => count($tablesData),
            'total_rows'   => $totalRows,
        ];
    }

    /**
     * Generate array-based DB::table()->insert() code chunks.
     */
    private function generateArrayInsertRunMethod(array $tablesData, int $chunkSize, bool $truncate): string
    {
        $lines = [];

        foreach ($tablesData as $tableName => $tableInfo) {
            $columns = $tableInfo['columns'];
            $rows = $tableInfo['rows'];
            $rowCount = count($rows);

            if ($rowCount === 0) {
                continue;
            }

            if ($truncate) {
                $lines[] = "        DB::table('{$tableName}')->truncate();";
                $lines[] = "";
            }

            $chunks = array_chunk($rows, $chunkSize);
            $chunkCount = count($chunks);

            foreach ($chunks as $chunkIndex => $chunk) {
                if ($chunkCount > 1) {
                    $startNum = ($chunkIndex * $chunkSize) + 1;
                    $endNum = min(($chunkIndex + 1) * $chunkSize, $rowCount);
                    $lines[] = "        // {$tableName}: rows {$startNum} to {$endNum} of {$rowCount}";
                }

                $lines[] = "        DB::table('{$tableName}')->insert([";

                foreach ($chunk as $row) {
                    $rowItems = [];

                    if ($columns !== null && count($columns) === count($row)) {
                        for ($i = 0; $i < count($columns); $i++) {
                            $colName = $columns[$i];
                            $val = $row[$i];
                            $rowItems[] = var_export($colName, true) . " => " . $this->formatPhpValue($val);
                        }
                    } else {
                        // Positional row without named columns
                        foreach ($row as $val) {
                            $rowItems[] = $this->formatPhpValue($val);
                        }
                    }

                    $lines[] = "            [" . implode(', ', $rowItems) . "],";
                }

                $lines[] = "        ]);";
                $lines[] = "";
            }
        }

        return rtrim(implode("\n", $lines));
    }

    /**
     * Generate raw SQL DB::unprepared() execution code.
     */
    private function generateRawSqlRunMethod(string $rawSql, array $tablesData, bool $truncate): string
    {
        $lines = [];

        if ($truncate) {
            foreach (array_keys($tablesData) as $tableName) {
                $lines[] = "        DB::table('{$tableName}')->truncate();";
            }
            $lines[] = "";
        }

        // Extract all INSERT statements
        $statements = $this->extractInsertStatements($rawSql);
        $cleanSql = implode(";\n", $statements) . (count($statements) ? ";" : "");

        $lines[] = "        \$sql = <<<'SQL'";
        $lines[] = $cleanSql;
        $lines[] = "SQL;";
        $lines[] = "        DB::unprepared(\$sql);";

        return implode("\n", $lines);
    }

    /**
     * Format a parsed value into valid PHP code.
     */
    private function formatPhpValue(mixed $value): string
    {
        if ($value === null) {
            return 'null';
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_int($value) || is_float($value)) {
            return (string)$value;
        }

        return var_export($value, true);
    }

    /**
     * Extract INSERT INTO / REPLACE INTO statements from SQL, skipping comments and non-insert queries.
     *
     * @param string $sql
     * @return string[]
     */
    public function extractInsertStatements(string $sql): array
    {
        $statements = [];
        $length = strlen($sql);
        $i = 0;

        $inSingleQuote = false;
        $inDoubleQuote = false;
        $inBacktick    = false;
        $inLineComment = false;
        $inBlockComment = false;

        $currentStmt = '';
        $isInsert = false;

        while ($i < $length) {
            $char = $sql[$i];
            $nextChar = ($i + 1 < $length) ? $sql[$i + 1] : '';

            // Handle line comments (-- or #)
            if (!$inSingleQuote && !$inDoubleQuote && !$inBacktick && !$inBlockComment) {
                if (($char === '-' && $nextChar === '-') || $char === '#') {
                    $inLineComment = true;
                }
            }

            if ($inLineComment) {
                if ($char === "\n") {
                    $inLineComment = false;
                }
                $i++;
                continue;
            }

            // Handle block comments (/* ... */)
            if (!$inSingleQuote && !$inDoubleQuote && !$inBacktick && !$inLineComment) {
                if ($char === '/' && $nextChar === '*') {
                    $inBlockComment = true;
                    $i += 2;
                    continue;
                }
            }

            if ($inBlockComment) {
                if ($char === '*' && $nextChar === '/') {
                    $inBlockComment = false;
                    $i += 2;
                    continue;
                }
                $i++;
                continue;
            }

            // Handle quote states
            if ($char === "'" && !$inDoubleQuote && !$inBacktick) {
                // Check if escaped with backslash
                $escaped = false;
                $k = $i - 1;
                while ($k >= 0 && $sql[$k] === '\\') {
                    $escaped = !$escaped;
                    $k--;
                }
                if (!$escaped) {
                    // Check for SQL '' escape
                    if ($inSingleQuote && $nextChar === "'") {
                        $currentStmt .= "''";
                        $i += 2;
                        continue;
                    }
                    $inSingleQuote = !$inSingleQuote;
                }
            } elseif ($char === '"' && !$inSingleQuote && !$inBacktick) {
                $escaped = false;
                $k = $i - 1;
                while ($k >= 0 && $sql[$k] === '\\') {
                    $escaped = !$escaped;
                    $k--;
                }
                if (!$escaped) {
                    if ($inDoubleQuote && $nextChar === '"') {
                        $currentStmt .= '""';
                        $i += 2;
                        continue;
                    }
                    $inDoubleQuote = !$inDoubleQuote;
                }
            } elseif ($char === '`' && !$inSingleQuote && !$inDoubleQuote) {
                $inBacktick = !$inBacktick;
            }

            $currentStmt .= $char;

            // Check if this statement is an INSERT or REPLACE statement
            if (!$isInsert && strlen($currentStmt) < 50) {
                $trimmed = ltrim($currentStmt);
                if (preg_match('/^(INSERT|REPLACE)\b/i', $trimmed)) {
                    $isInsert = true;
                }
            }

            // Check for statement end (semicolon outside of quotes)
            if ($char === ';' && !$inSingleQuote && !$inDoubleQuote && !$inBacktick) {
                if ($isInsert) {
                    $trimmed = trim(substr($currentStmt, 0, -1));
                    if (!empty($trimmed)) {
                        $statements[] = $trimmed;
                    }
                }
                $currentStmt = '';
                $isInsert = false;
            }

            $i++;
        }

        // Catch trailing statement without semicolon
        if ($isInsert) {
            $trimmed = trim($currentStmt);
            if (!empty($trimmed)) {
                $statements[] = $trimmed;
            }
        }

        return $statements;
    }

    /**
     * Parse a single INSERT / REPLACE INTO statement into table, columns, and rows.
     *
     * @param string $stmt
     * @return array{table: string, columns: ?array, rows: array}|null
     */
    public function parseInsertStatement(string $stmt): ?array
    {
        // Match table name and optional column definitions:
        // INSERT [IGNORE] INTO [`"]?tableName[`"]? [ ( `col1`, `col2` ) ] VALUES ...
        $pattern = '/^(?:INSERT\s+(?:IGNORE\s+)?INTO|REPLACE\s+INTO)\s+(?:`?([a-zA-Z0-9_\-]+)`?\.)?`?([a-zA-Z0-9_\-]+)`?\s*(?:\((.*?)\))?\s*VALUES\s*/is';

        if (!preg_match($pattern, $stmt, $matches, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $tableName = $matches[2][0];
        $columnsRaw = !empty($matches[3][0]) ? $matches[3][0] : null;
        $valuesStartOffset = $matches[0][1] + strlen($matches[0][0]);

        $columns = null;
        if ($columnsRaw !== null) {
            $columns = array_map(function ($col) {
                return trim($col, " `\"'\t\n\r\0\x0B");
            }, explode(',', $columnsRaw));
        }

        $valuesSql = substr($stmt, $valuesStartOffset);
        $rows = $this->parseValuesClause($valuesSql);

        return [
            'table'   => $tableName,
            'columns' => $columns,
            'rows'    => $rows,
        ];
    }

    /**
     * Parse the `(row1), (row2), ...` portion of a VALUES clause into array of rows.
     *
     * @param string $valuesSql
     * @return array<array<mixed>>
     */
    public function parseValuesClause(string $valuesSql): array
    {
        $rows = [];
        $length = strlen($valuesSql);
        $i = 0;

        $inRow = false;
        $currentRowSql = '';
        $inSingleQuote = false;
        $inDoubleQuote = false;
        $depth = 0;

        while ($i < $length) {
            $char = $valuesSql[$i];
            $nextChar = ($i + 1 < $length) ? $valuesSql[$i + 1] : '';

            if ($char === "'" && !$inDoubleQuote) {
                $escaped = false;
                $k = $i - 1;
                while ($k >= 0 && $valuesSql[$k] === '\\') {
                    $escaped = !$escaped;
                    $k--;
                }
                if (!$escaped) {
                    if ($inSingleQuote && $nextChar === "'") {
                        $currentRowSql .= "''";
                        $i += 2;
                        continue;
                    }
                    $inSingleQuote = !$inSingleQuote;
                }
            } elseif ($char === '"' && !$inSingleQuote) {
                $escaped = false;
                $k = $i - 1;
                while ($k >= 0 && $valuesSql[$k] === '\\') {
                    $escaped = !$escaped;
                    $k--;
                }
                if (!$escaped) {
                    if ($inDoubleQuote && $nextChar === '"') {
                        $currentRowSql .= '""';
                        $i += 2;
                        continue;
                    }
                    $inDoubleQuote = !$inDoubleQuote;
                }
            }

            if (!$inSingleQuote && !$inDoubleQuote) {
                if ($char === '(') {
                    if ($depth === 0) {
                        $inRow = true;
                        $currentRowSql = '';
                        $depth++;
                        $i++;
                        continue;
                    }
                    $depth++;
                } elseif ($char === ')') {
                    $depth--;
                    if ($depth === 0 && $inRow) {
                        $inRow = false;
                        $rows[] = $this->parseSingleRowValues($currentRowSql);
                        $currentRowSql = '';
                        $i++;
                        continue;
                    }
                }
            }

            if ($inRow) {
                $currentRowSql .= $char;
            }

            $i++;
        }

        return $rows;
    }

    /**
     * Parse comma-separated value literals within a single row `val1, 'val2', NULL, 123`.
     *
     * @param string $rowSql
     * @return array<mixed>
     */
    public function parseSingleRowValues(string $rowSql): array
    {
        $values = [];
        $length = strlen($rowSql);
        $i = 0;

        $inSingleQuote = false;
        $inDoubleQuote = false;
        $currentVal = '';
        $nestedParenDepth = 0;

        while ($i < $length) {
            $char = $rowSql[$i];
            $nextChar = ($i + 1 < $length) ? $rowSql[$i + 1] : '';

            if ($char === "'" && !$inDoubleQuote) {
                $escaped = false;
                $k = $i - 1;
                while ($k >= 0 && $rowSql[$k] === '\\') {
                    $escaped = !$escaped;
                    $k--;
                }
                if (!$escaped) {
                    if ($inSingleQuote && $nextChar === "'") {
                        $currentVal .= "'";
                        $i += 2;
                        continue;
                    }
                    $inSingleQuote = !$inSingleQuote;
                    $currentVal .= $char;
                    $i++;
                    continue;
                }
            } elseif ($char === '"' && !$inSingleQuote) {
                $escaped = false;
                $k = $i - 1;
                while ($k >= 0 && $rowSql[$k] === '\\') {
                    $escaped = !$escaped;
                    $k--;
                }
                if (!$escaped) {
                    if ($inDoubleQuote && $nextChar === '"') {
                        $currentVal .= '"';
                        $i += 2;
                        continue;
                    }
                    $inDoubleQuote = !$inDoubleQuote;
                    $currentVal .= $char;
                    $i++;
                    continue;
                }
            }

            if (!$inSingleQuote && !$inDoubleQuote) {
                if ($char === '(') {
                    $nestedParenDepth++;
                } elseif ($char === ')') {
                    $nestedParenDepth--;
                } elseif ($char === ',' && $nestedParenDepth === 0) {
                    $values[] = $this->interpretSqlLiteral(trim($currentVal));
                    $currentVal = '';
                    $i++;
                    continue;
                }
            }

            $currentVal .= $char;
            $i++;
        }

        if (strlen(trim($currentVal)) > 0 || count($values) > 0) {
            $values[] = $this->interpretSqlLiteral(trim($currentVal));
        }

        return $values;
    }

    /**
     * Convert an individual SQL literal string into its appropriate PHP type.
     *
     * @param string $literal
     * @return mixed
     */
    private function interpretSqlLiteral(string $literal): mixed
    {
        if (strcasecmp($literal, 'NULL') === 0) {
            return null;
        }

        if (strcasecmp($literal, 'TRUE') === 0) {
            return true;
        }

        if (strcasecmp($literal, 'FALSE') === 0) {
            return false;
        }

        // Single quoted string
        if (str_starts_with($literal, "'") && str_ends_with($literal, "'") && strlen($literal) >= 2) {
            $unquoted = substr($literal, 1, -1);
            return $this->unescapeSqlString($unquoted);
        }

        // Double quoted string
        if (str_starts_with($literal, '"') && str_ends_with($literal, '"') && strlen($literal) >= 2) {
            $unquoted = substr($literal, 1, -1);
            return $this->unescapeSqlString($unquoted);
        }

        // Numeric values
        if (preg_match('/^-?\d+$/', $literal)) {
            return (int)$literal;
        }

        if (preg_match('/^-?\d+\.\d+$/', $literal)) {
            return (float)$literal;
        }

        // Hex literals (e.g. 0x48656c6c6f)
        if (preg_match('/^0x[0-9a-fA-F]+$/', $literal)) {
            $hex = substr($literal, 2);
            $bin = hex2bin($hex);
            return $bin !== false ? $bin : $literal;
        }

        return $literal;
    }

    /**
     * Unescape standard MySQL / SQL escape characters in a string value.
     */
    private function unescapeSqlString(string $str): string
    {
        $search = ["\\'", '\\"', '\\\\', '\\0', '\\b', '\\n', '\\r', '\\t', '\\Z', "''"];
        $replace = ["'", '"', '\\', "\0", "\x08", "\n", "\r", "\t", "\x1A", "'"];

        return str_replace($search, $replace, $str);
    }
}
