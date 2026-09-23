<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\BackupNotification;
use Carbon\Carbon;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:database 
                            {--compress : Compress the backup file}
                            {--encrypt : Encrypt the backup file}
                            {--upload : Upload to cloud storage}
                            {--notify : Send notification email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a comprehensive database backup with optional compression, encryption, and cloud upload';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startTime = microtime(true);
        $this->info('🚀 Starting database backup process...');

        try {
            // Generate backup filename
            $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
            $filename = "database_backup_{$timestamp}.sql";
            
            // Create backup directory if it doesn't exist
            $backupPath = storage_path('app/backups/database');
            if (!file_exists($backupPath)) {
                mkdir($backupPath, 0755, true);
            }
            
            $fullPath = $backupPath . '/' . $filename;
            
            // Get database configuration
            $connection = config('database.default');
            $config = config("database.connections.{$connection}");
            
            // Create database dump
            $this->info('📊 Creating database dump...');
            $this->createDatabaseDump($config, $fullPath);
            
            // Verify backup file
            if (!file_exists($fullPath) || filesize($fullPath) === 0) {
                throw new \Exception('Backup file was not created or is empty');
            }
            
            $fileSize = $this->formatBytes(filesize($fullPath));
            $this->info("✅ Database backup created: {$filename} ({$fileSize})");
            
            // Compress if requested
            if ($this->option('compress')) {
                $fullPath = $this->compressBackup($fullPath);
                $filename = basename($fullPath);
            }
            
            // Encrypt if requested
            if ($this->option('encrypt')) {
                $fullPath = $this->encryptBackup($fullPath);
                $filename = basename($fullPath);
            }
            
            // Upload to cloud if requested
            if ($this->option('upload')) {
                $this->uploadToCloud($fullPath, $filename);
            }
            
            // Clean old backups
            $this->cleanOldBackups();
            
            // Log success
            $duration = round(microtime(true) - $startTime, 2);
            $this->info("🎉 Backup completed successfully in {$duration} seconds");
            
            Log::info('Database backup completed', [
                'filename' => $filename,
                'size' => filesize($fullPath),
                'duration' => $duration,
                'compressed' => $this->option('compress'),
                'encrypted' => $this->option('encrypt'),
                'uploaded' => $this->option('upload'),
            ]);
            
            // Send notification if requested
            if ($this->option('notify')) {
                $this->sendNotification($filename, filesize($fullPath), $duration);
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Backup failed: " . $e->getMessage());
            Log::error('Database backup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return Command::FAILURE;
        }
        
        return Command::SUCCESS;
    }
    
    /**
     * Create database dump using mysqldump.
     */
    protected function createDatabaseDump(array $config, string $filePath): void
    {
        $host = $config['host'];
        $port = $config['port'];
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];
        
        // Build mysqldump command
        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers --add-drop-table %s > %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($filePath)
        );
        
        // Execute command
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \Exception('mysqldump command failed with return code: ' . $returnCode);
        }
    }
    
    /**
     * Compress backup file.
     */
    protected function compressBackup(string $filePath): string
    {
        $this->info('🗜️ Compressing backup file...');
        
        $compressedPath = $filePath . '.gz';
        
        $command = sprintf('gzip -c %s > %s', escapeshellarg($filePath), escapeshellarg($compressedPath));
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \Exception('Compression failed');
        }
        
        // Remove original file
        unlink($filePath);
        
        $this->info('✅ Backup compressed successfully');
        return $compressedPath;
    }
    
    /**
     * Encrypt backup file.
     */
    protected function encryptBackup(string $filePath): string
    {
        $this->info('🔐 Encrypting backup file...');
        
        $encryptedPath = $filePath . '.enc';
        $key = config('app.key');
        
        $data = file_get_contents($filePath);
        $encryptedData = encrypt($data);
        
        file_put_contents($encryptedPath, $encryptedData);
        
        // Remove original file
        unlink($filePath);
        
        $this->info('✅ Backup encrypted successfully');
        return $encryptedPath;
    }
    
    /**
     * Upload backup to cloud storage.
     */
    protected function uploadToCloud(string $filePath, string $filename): void
    {
        $this->info('☁️ Uploading to cloud storage...');
        
        $disk = Storage::disk('s3'); // or your preferred cloud disk
        $cloudPath = 'backups/database/' . $filename;
        
        $disk->put($cloudPath, file_get_contents($filePath));
        
        $this->info('✅ Backup uploaded to cloud storage');
    }
    
    /**
     * Clean old backup files.
     */
    protected function cleanOldBackups(): void
    {
        $this->info('🧹 Cleaning old backups...');
        
        $backupPath = storage_path('app/backups/database');
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
     * Send backup notification.
     */
    protected function sendNotification(string $filename, int $fileSize, float $duration): void
    {
        $adminEmail = config('mail.admin_email');
        if ($adminEmail) {
            Mail::to($adminEmail)->send(new BackupNotification($filename, $fileSize, $duration));
            $this->info('📧 Notification email sent');
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
