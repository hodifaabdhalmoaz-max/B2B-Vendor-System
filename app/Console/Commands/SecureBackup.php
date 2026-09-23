<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class SecureBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:secure {--encrypt : Encrypt the backup file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a secure backup of the database and important files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔒 بدء إنشاء النسخة الاحتياطية الآمنة...');

        try {
            $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
            $backupName = "backup_{$timestamp}";
            
            // إنشاء مجلد النسخ الاحتياطية
            $backupPath = storage_path("app/backups/{$backupName}");
            File::makeDirectory($backupPath, 0755, true);

            // نسخ احتياطية لقاعدة البيانات
            $this->info('📊 إنشاء نسخة احتياطية لقاعدة البيانات...');
            $this->backupDatabase($backupPath);

            // نسخ احتياطية للملفات المهمة
            $this->info('📁 نسخ الملفات المهمة...');
            $this->backupImportantFiles($backupPath);

            // ضغط النسخة الاحتياطية
            $this->info('🗜️ ضغط النسخة الاحتياطية...');
            $zipPath = $this->compressBackup($backupPath, $backupName);

            // تشفير النسخة الاحتياطية إذا طُلب ذلك
            if ($this->option('encrypt')) {
                $this->info('🔐 تشفير النسخة الاحتياطية...');
                $zipPath = $this->encryptBackup($zipPath);
            }

            // تنظيف النسخ القديمة
            $this->cleanOldBackups();

            // إنشاء تقرير النسخة الاحتياطية
            $this->createBackupReport($backupName, $zipPath);

            $this->info("✅ تم إنشاء النسخة الاحتياطية بنجاح: {$zipPath}");

        } catch (\Exception $e) {
            $this->error("❌ فشل في إنشاء النسخة الاحتياطية: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Backup database
     */
    protected function backupDatabase(string $backupPath): void
    {
        $dbConfig = config('database.connections.' . config('database.default'));
        
        $dumpCommand = sprintf(
            'mysqldump --user=%s --password=%s --host=%s --port=%s --single-transaction --routines --triggers %s > %s',
            escapeshellarg($dbConfig['username']),
            escapeshellarg($dbConfig['password']),
            escapeshellarg($dbConfig['host']),
            escapeshellarg($dbConfig['port']),
            escapeshellarg($dbConfig['database']),
            escapeshellarg($backupPath . '/database.sql')
        );

        $result = null;
        $output = [];
        exec($dumpCommand, $output, $result);

        if ($result !== 0) {
            throw new \Exception('فشل في إنشاء نسخة احتياطية لقاعدة البيانات');
        }

        // إنشاء ملف معلومات قاعدة البيانات
        $dbInfo = [
            'database' => $dbConfig['database'],
            'host' => $dbConfig['host'],
            'port' => $dbConfig['port'],
            'charset' => $dbConfig['charset'],
            'backup_time' => now()->toISOString(),
            'tables_count' => DB::select("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = ?", [$dbConfig['database']])[0]->count ?? 0,
        ];

        File::put($backupPath . '/database_info.json', json_encode($dbInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Backup important files
     */
    protected function backupImportantFiles(string $backupPath): void
    {
        $importantPaths = [
            '.env' => 'config/.env',
            'storage/app/public' => 'uploads/',
            'public/assets/images' => 'assets/images/',
            'config/' => 'config/app_config/',
        ];

        foreach ($importantPaths as $source => $destination) {
            $sourcePath = base_path($source);
            $destPath = $backupPath . '/' . $destination;

            if (File::exists($sourcePath)) {
                if (File::isDirectory($sourcePath)) {
                    File::copyDirectory($sourcePath, $destPath);
                } else {
                    File::ensureDirectoryExists(dirname($destPath));
                    File::copy($sourcePath, $destPath);
                }
            }
        }

        // إنشاء ملف معلومات النسخة الاحتياطية
        $backupInfo = [
            'app_name' => config('app.name'),
            'app_version' => '1.0.0',
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'backup_time' => now()->toISOString(),
            'backup_size' => $this->getDirectorySize($backupPath),
            'files_count' => $this->countFiles($backupPath),
        ];

        File::put($backupPath . '/backup_info.json', json_encode($backupInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Compress backup
     */
    protected function compressBackup(string $backupPath, string $backupName): string
    {
        $zipPath = storage_path("app/backups/{$backupName}.zip");
        
        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
            throw new \Exception('فشل في إنشاء ملف ZIP');
        }

        $this->addDirectoryToZip($zip, $backupPath, '');
        $zip->close();

        // حذف المجلد المؤقت
        File::deleteDirectory($backupPath);

        return $zipPath;
    }

    /**
     * Add directory to ZIP
     */
    protected function addDirectoryToZip(\ZipArchive $zip, string $dir, string $base): void
    {
        $files = File::allFiles($dir);
        
        foreach ($files as $file) {
            $relativePath = $base . str_replace($dir . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $zip->addFile($file->getPathname(), $relativePath);
        }
    }

    /**
     * Encrypt backup
     */
    protected function encryptBackup(string $zipPath): string
    {
        $encryptedPath = $zipPath . '.encrypted';
        $content = File::get($zipPath);
        $encrypted = Crypt::encrypt($content);
        
        File::put($encryptedPath, $encrypted);
        File::delete($zipPath);
        
        return $encryptedPath;
    }

    /**
     * Clean old backups
     */
    protected function cleanOldBackups(): void
    {
        $backupDir = storage_path('app/backups');
        $retentionDays = config('security.backup.retention_days', 30);
        
        if (!File::exists($backupDir)) {
            return;
        }

        $files = File::files($backupDir);
        $cutoffTime = Carbon::now()->subDays($retentionDays);

        foreach ($files as $file) {
            if (Carbon::createFromTimestamp($file->getMTime())->lt($cutoffTime)) {
                File::delete($file->getPathname());
                $this->line("🗑️ حذف النسخة الاحتياطية القديمة: " . $file->getFilename());
            }
        }
    }

    /**
     * Create backup report
     */
    protected function createBackupReport(string $backupName, string $backupPath): void
    {
        $report = [
            'backup_name' => $backupName,
            'backup_path' => $backupPath,
            'backup_size' => File::size($backupPath),
            'backup_time' => now()->toISOString(),
            'encrypted' => $this->option('encrypt'),
            'status' => 'completed',
        ];

        $reportPath = storage_path('app/backups/backup_reports.json');
        $reports = [];
        
        if (File::exists($reportPath)) {
            $reports = json_decode(File::get($reportPath), true) ?? [];
        }
        
        $reports[] = $report;
        
        // الاحتفاظ بآخر 100 تقرير فقط
        if (count($reports) > 100) {
            $reports = array_slice($reports, -100);
        }
        
        File::put($reportPath, json_encode($reports, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Get directory size
     */
    protected function getDirectorySize(string $path): int
    {
        $size = 0;
        $files = File::allFiles($path);
        
        foreach ($files as $file) {
            $size += $file->getSize();
        }
        
        return $size;
    }

    /**
     * Count files in directory
     */
    protected function countFiles(string $path): int
    {
        return count(File::allFiles($path));
    }
}
