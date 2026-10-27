<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * =============================================================================
 * BackupController
 * =============================================================================
 *
 * Manages MySQL database backup files (.sql) for the CNAT application.
 * All endpoints are restricted to Admin / Developer / Owner roles (Tier 1).
 *
 * Backup storage : storage/app/backups/
 * Filename format: cnat_db_backup_YYYY-MM-DD_HH-MM-SS.sql
 * Dump tool      : mysqldump (WampServer or environment path)
 *
 * Endpoints (prefix: /api/backups)
 * ---------------------------------
 *  GET    /api/backups              -> index()      List all backup files
 *  POST   /api/backups              -> create()     Generate a new SQL backup
 *  GET    /api/backups/{filename}   -> download()   Download a backup file
 *  DELETE /api/backups/{filename}   -> destroy()    Delete a single backup
 *  DELETE /api/backups              -> destroyAll() Delete all backups
 * =============================================================================
 */
class BackupController extends Controller
{
    /**
     * Default absolute path to the mysqldump binary (WampServer on E:\).
     */
    private const DEFAULT_MYSQLDUMP_PATH = 'E:\\wamp64\\bin\\mysql\\mysql9.1.0\\bin\\mysqldump.exe';

    /**
     * Storage disk sub-directory where .sql files are kept.
     * Maps to storage/app/backups/
     */
    private const BACKUP_DIR = 'backups';

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Resolves the mysqldump binary executable path.
     */
    public function getMysqldumpPath(): string
    {
        $configured = env('MYSQLDUMP_PATH');
        if (!empty($configured) && file_exists($configured)) {
            return $configured;
        }

        if (file_exists(self::DEFAULT_MYSQLDUMP_PATH)) {
            return self::DEFAULT_MYSQLDUMP_PATH;
        }

        // Try finding mysqldump from system PATH
        $which = PHP_OS_FAMILY === 'Windows'
            ? @exec('where mysqldump 2>nul')
            : @exec('which mysqldump 2>/dev/null');

        if (!empty($which) && file_exists($which)) {
            return $which;
        }

        return self::DEFAULT_MYSQLDUMP_PATH;
    }

    public function backupDiskPath(): string
    {
        $path = storage_path('app' . DIRECTORY_SEPARATOR . self::BACKUP_DIR);
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        return $path;
    }

    private function sanitizeFilename(string $filename): string
    {
        $clean = basename($filename);
        if (!preg_match('/^[a-zA-Z0-9_\-\.]+\.sql$/', $clean)) {
            throw new \InvalidArgumentException('Invalid backup filename.');
        }
        return $clean;
    }

    private function formatSize(int $bytes): string
    {
        if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576)   return round($bytes / 1048576, 2)   . ' MB';
        if ($bytes >= 1024)      return round($bytes / 1024, 2)      . ' KB';
        return $bytes . ' B';
    }

    // -------------------------------------------------------------------------
    // GET /api/backups  ->  List all backup files
    // -------------------------------------------------------------------------
    public function index()
    {
        $dir   = $this->backupDiskPath();
        $files = glob($dir . DIRECTORY_SEPARATOR . '*.sql') ?: [];
        usort($files, fn($a, $b) => filemtime($b) - filemtime($a));

        $backups = array_map(function (string $filePath) {
            $size = filesize($filePath);
            return [
                'filename'   => basename($filePath),
                'size_bytes' => $size,
                'size_human' => $this->formatSize($size),
                'created_at' => date(DATE_ATOM, filemtime($filePath)),
            ];
        }, $files);

        return ResponseHelper::success('Backup list retrieved successfully.', [
            'count'   => count($backups),
            'backups' => $backups,
        ]);
    }

    // -------------------------------------------------------------------------
    // POST /api/backups  ->  Create a new SQL backup
    // -------------------------------------------------------------------------
    public function create()
    {
        $mysqldump = $this->getMysqldumpPath();

        if (!file_exists($mysqldump)) {
            Log::error('mysqldump binary not found', ['path' => $mysqldump]);
            return ResponseHelper::error(
                "mysqldump binary not found at: {$mysqldump}. Please set MYSQLDUMP_PATH in .env or verify MySQL installation.",
                null,
                500
            );
        }

        $host     = config('database.connections.mysql.host',     '127.0.0.1');
        $port     = config('database.connections.mysql.port',     '3306');
        $database = config('database.connections.mysql.database', '');
        $username = config('database.connections.mysql.username', '');
        $password = config('database.connections.mysql.password', '');

        if (empty($database)) {
            return ResponseHelper::error('Database name not configured.', null, 500);
        }

        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename  = "{$database}_backup_{$timestamp}.sql";
        $filePath  = $this->backupDiskPath() . DIRECTORY_SEPARATOR . $filename;

        $fp = fopen($filePath, 'wb');
        if (!$fp) {
            Log::error('Could not open file for writing backup', ['path' => $filePath]);
            return ResponseHelper::error('Failed to create backup file on disk.', null, 500);
        }

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => $fp,            // stdout directly to file stream
            2 => ['pipe', 'w'],  // stderr to pipe for capturing errors
        ];

        // Construct mysqldump command
        $cmd = "\"{$mysqldump}\" --host={$host} --port={$port} --user={$username} --single-transaction --routines --triggers --add-drop-table {$database}";

        // Inject MYSQL_PWD into child process environment (avoids CLI password exposure and Windows cmd quoting issues)
        $env = array_merge($_ENV, getenv(), [
            'MYSQL_PWD' => (string)$password,
        ]);

        $process = proc_open($cmd, $descriptors, $pipes, null, $env);

        if (!is_resource($process)) {
            fclose($fp);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            Log::error('proc_open failed to spawn mysqldump process');
            return ResponseHelper::error('Failed to execute mysqldump process.', null, 500);
        }

        // Close stdin pipe
        fclose($pipes[0]);

        // Capture stderr
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        // Close file handle
        fclose($fp);

        $exitCode = proc_close($process);

        if ($exitCode !== 0 || !file_exists($filePath) || filesize($filePath) === 0) {
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $errorMsg = trim($stderr);
            Log::error('Database backup failed', ['exit_code' => $exitCode, 'error' => $errorMsg]);

            return ResponseHelper::error(
                'Database backup failed. Check server logs for details.',
                ['mysqldump_error' => $errorMsg, 'exit_code' => $exitCode],
                500
            );
        }

        $size = filesize($filePath);
        Log::info('Database backup created successfully', [
            'filename' => $filename,
            'size'     => $size,
        ]);

        return ResponseHelper::success('Database backup created successfully.', [
            'filename'   => $filename,
            'size_bytes' => $size,
            'size_human' => $this->formatSize($size),
            'created_at' => date(DATE_ATOM, filemtime($filePath)),
        ], 201);
    }

    // -------------------------------------------------------------------------
    // GET /api/backups/{filename}  ->  Download a backup file
    // -------------------------------------------------------------------------
    public function download(string $filename)
    {
        try {
            $clean    = $this->sanitizeFilename($filename);
            $filePath = $this->backupDiskPath() . DIRECTORY_SEPARATOR . $clean;

            if (!file_exists($filePath)) {
                return ResponseHelper::error('Backup file not found.', null, 404);
            }

            Log::info('Backup file download initiated', ['filename' => $clean]);

            return response()->download($filePath, $clean, [
                'Content-Type'        => 'application/octet-stream',
                'Content-Disposition' => "attachment; filename=\"{$clean}\"",
            ]);

        } catch (\InvalidArgumentException $e) {
            return ResponseHelper::error($e->getMessage(), null, 422);
        }
    }

    // -------------------------------------------------------------------------
    // DELETE /api/backups/{filename}  ->  Delete a single backup
    // -------------------------------------------------------------------------
    public function destroy(string $filename)
    {
        try {
            $clean    = $this->sanitizeFilename($filename);
            $filePath = $this->backupDiskPath() . DIRECTORY_SEPARATOR . $clean;

            if (!file_exists($filePath)) {
                return ResponseHelper::error('Backup file not found.', null, 404);
            }

            unlink($filePath);
            Log::info('Backup file deleted', ['filename' => $clean]);

            return ResponseHelper::success('Backup deleted successfully.', ['filename' => $clean]);

        } catch (\InvalidArgumentException $e) {
            return ResponseHelper::error($e->getMessage(), null, 422);
        }
    }

    // -------------------------------------------------------------------------
    // DELETE /api/backups  ->  Delete ALL backup files
    // -------------------------------------------------------------------------
    public function destroyAll(Request $request)
    {
        if (!$request->boolean('confirm')) {
            return ResponseHelper::error(
                'Confirmation required. Send { "confirm": true } in the request body to delete all backups.',
                null,
                422
            );
        }

        $dir   = $this->backupDiskPath();
        $files = glob($dir . DIRECTORY_SEPARATOR . '*.sql') ?: [];
        $count = 0;

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
                $count++;
            }
        }

        Log::warning('All backup files deleted', [
            'deleted_count' => $count,
            'deleted_by'    => auth()->id(),
        ]);

        return ResponseHelper::success('All backups deleted successfully.', [
            'deleted_count' => $count,
        ]);
    }
}
