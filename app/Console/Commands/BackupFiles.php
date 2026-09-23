<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BackupFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:files 
                            {--exclude=* : Directories to exclude from backup}
                            {--upload : Upload to cloud storage}
                            {--verify : Verify backup integrity}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a comprehensive backup of application files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startTime = microtime(true);
        $this->info('🚀 Starting files backup process...');

        try {
            // Generate backup filename
            $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
            $filename = "files_backup_{$timestamp}.tar.gz";
            
            // Create backup directory if it doesn't exist
            $backupPath = storage_path('app/backups/files');
            if (!file_exists($backupPath)) {
                mkdir($backupPath, 0755, true);
            }
            
            $fullPath = $backupPath . '/' . $filename;
            
            // Define directories to backup
            $backupDirs = $this->getBackupDirectories();
            
            // Create tar archive
            $this->info('📦 Creating files archive...');
            $this->createFilesArchive($backupDirs, $fullPath);
            
            // Verify backup file
            if (!file_exists($fullPath) || filesize($fullPath) === 0) {
                throw new \Exception('Backup file was not created or is empty');
            }
            
            $fileSize = $this->formatBytes(filesize($fullPath));
            $this->info("✅ Files backup created: {$filename} ({$fileSize})");
            
            // Verify backup integrity if requested
            if ($this->option('verify')) {
                $this->verifyBackupIntegrity($fullPath);
            }
            
            // Upload to cloud if requested
            if ($this->option('upload')) {
                $this->uploadToCloud($fullPath, $filename);
            }
            
            // Clean old backups
            $this->cleanOldBackups();
            
            // Log success
            $duration = round(microtime(true) - $startTime, 2);
            $this->info("🎉 Files backup completed successfully in {$duration} seconds");
            
            Log::info('Files backup completed', [
                'filename' => $filename,
                'size' => filesize($fullPath),
                'duration' => $duration,
                'uploaded' => $this->option('upload'),
                'verified' => $this->option('verify'),
            ]);
            
        } catch (\Exception $e) {
            $this->error("❌ Files backup failed: " . $e->getMessage());
            Log::error('Files backup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return Command::FAILURE;
        }
        
        return Command::SUCCESS;
    }
    
    /**
     * Get directories to backup.
     */
    protected function getBackupDirectories(): array
    {
        $basePath = base_path();
        $excludeDefaults = [
            'node_modules',
            'vendor',
            '.git',
            'storage/logs',
            'storage/framework/cache',
            'storage/framework/sessions',
            'storage/framework/views',
            'bootstrap/cache',
            '.env',
            '.env.*',
        ];
        
        $excludeCustom = $this->option('exclude') ?? [];
        $excludeAll = array_merge($excludeDefaults, $excludeCustom);
        
        // Important directories to backup
        $importantDirs = [
            'app',
            'config',
            'database',
            'public',
            'resources',
            'routes',
            'storage/app',
            'storage/uploads',
            'lang',
        ];
        
        $backupDirs = [];
        foreach ($importantDirs as $dir) {
            $fullDir = $basePath . '/' . $dir;
            if (is_dir($fullDir)) {
                $backupDirs[] = $dir;
            }
        }
        
        return [
            'base_path' => $basePath,
            'directories' => $backupDirs,
            'exclude' => $excludeAll,
        ];
    }
    
    /**
     * Create files archive using tar.
     */
    protected function createFilesArchive(array $config, string $outputPath): void
    {
        $basePath = $config['base_path'];
        $directories = $config['directories'];
        $exclude = $config['exclude'];
        
        // Build exclude parameters
        $excludeParams = '';
        foreach ($exclude as $excludeItem) {
            $excludeParams .= " --exclude='{$excludeItem}'";
        }
        
        // Build tar command
        $dirsString = implode(' ', $directories);
        $command = sprintf(
            'cd %s && tar -czf %s %s %s',
            escapeshellarg($basePath),
            escapeshellarg($outputPath),
            $excludeParams,
            $dirsString
        );
        
        // Execute command
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \Exception('tar command failed with return code: ' . $returnCode);
        }
    }
    
    /**
     * Verify backup integrity.
     */
    protected function verifyBackupIntegrity(string $filePath): void
    {
        $this->info('🔍 Verifying backup integrity...');
        
        // Test tar file integrity
        $command = sprintf('tar -tzf %s > /dev/null', escapeshellarg($filePath));
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \Exception('Backup file integrity check failed');
        }
        
        $this->info('✅ Backup integrity verified');
    }
    
    /**
     * Upload backup to cloud storage.
     */
    protected function uploadToCloud(string $filePath, string $filename): void
    {
        $this->info('☁️ Uploading to cloud storage...');
        
        $disk = Storage::disk('s3'); // or your preferred cloud disk
        $cloudPath = 'backups/files/' . $filename;
        
        // Upload in chunks for large files
        $handle = fopen($filePath, 'rb');
        $disk->writeStream($cloudPath, $handle);
        fclose($handle);
        
        $this->info('✅ Backup uploaded to cloud storage');
    }
    
    /**
     * Clean old backup files.
     */
    protected function cleanOldBackups(): void
    {
        $this->info('🧹 Cleaning old backups...');
        
        $backupPath = storage_path('app/backups/files');
        $retentionDays = config('backup.retention_days', 30);
        
        $files = glob($backupPath . '/*');
        $cutoffTime = time() - ($retentionDays * 24 * 60 * 60);
        
        $deletedCount = 0;
        foreach ($files as $file) {
            if (filemtime($file) < $cutoffTime) {
                unlink($file);
                $deletedCount++;
            }
        }
        
        if ($deletedCount > 0) {
            $this->info("🗑️ Deleted {$deletedCount} old backup files");
        }
    }
    
    /**
     * Format bytes to human readable format.
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
