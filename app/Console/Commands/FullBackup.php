<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\BackupNotification;
use Carbon\Carbon;

class FullBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:full 
                            {--compress : Compress backup files}
                            {--encrypt : Encrypt backup files}
                            {--upload : Upload to cloud storage}
                            {--verify : Verify backup integrity}
                            {--notify : Send notification email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a complete backup of database and files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startTime = microtime(true);
        $this->info('🚀 Starting full backup process...');
        $this->info('📅 Backup started at: ' . Carbon::now()->format('Y-m-d H:i:s'));

        $results = [
            'database' => false,
            'files' => false,
            'errors' => [],
        ];

        try {
            // Create backup session log
            $sessionId = uniqid('backup_');
            Log::info('Full backup session started', ['session_id' => $sessionId]);

            // Backup database
            $this->info('');
            $this->info('📊 === DATABASE BACKUP ===');
            $results['database'] = $this->backupDatabase();

            // Backup files
            $this->info('');
            $this->info('📁 === FILES BACKUP ===');
            $results['files'] = $this->backupFiles();

            // Generate backup report
            $this->generateBackupReport($results, $startTime, $sessionId);

            // Send notification if requested
            if ($this->option('notify')) {
                $this->sendNotification($results, $startTime);
            }

            $duration = round(microtime(true) - $startTime, 2);
            $this->info('');
            $this->info("🎉 Full backup completed in {$duration} seconds");

            Log::info('Full backup session completed', [
                'session_id' => $sessionId,
                'duration' => $duration,
                'database_success' => $results['database'],
                'files_success' => $results['files'],
            ]);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Full backup failed: " . $e->getMessage());
            Log::error('Full backup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }

    /**
     * Backup database.
     */
    protected function backupDatabase(): bool
    {
        try {
            $options = [];
            
            if ($this->option('compress')) {
                $options['--compress'] = true;
            }
            
            if ($this->option('encrypt')) {
                $options['--encrypt'] = true;
            }
            
            if ($this->option('upload')) {
                $options['--upload'] = true;
            }

            $exitCode = Artisan::call('backup:database', $options);
            
            if ($exitCode === 0) {
                $this->info('✅ Database backup completed successfully');
                return true;
            } else {
                $this->error('❌ Database backup failed');
                return false;
            }

        } catch (\Exception $e) {
            $this->error('❌ Database backup error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Backup files.
     */
    protected function backupFiles(): bool
    {
        try {
            $options = [];
            
            if ($this->option('upload')) {
                $options['--upload'] = true;
            }
            
            if ($this->option('verify')) {
                $options['--verify'] = true;
            }

            $exitCode = Artisan::call('backup:files', $options);
            
            if ($exitCode === 0) {
                $this->info('✅ Files backup completed successfully');
                return true;
            } else {
                $this->error('❌ Files backup failed');
                return false;
            }

        } catch (\Exception $e) {
            $this->error('❌ Files backup error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate backup report.
     */
    protected function generateBackupReport(array $results, float $startTime, string $sessionId): void
    {
        $duration = round(microtime(true) - $startTime, 2);
        $timestamp = Carbon::now()->format('Y-m-d H:i:s');
        
        $report = [
            'session_id' => $sessionId,
            'timestamp' => $timestamp,
            'duration' => $duration,
            'database_backup' => $results['database'] ? 'SUCCESS' : 'FAILED',
            'files_backup' => $results['files'] ? 'SUCCESS' : 'FAILED',
            'overall_status' => ($results['database'] && $results['files']) ? 'SUCCESS' : 'PARTIAL/FAILED',
            'options' => [
                'compress' => $this->option('compress'),
                'encrypt' => $this->option('encrypt'),
                'upload' => $this->option('upload'),
                'verify' => $this->option('verify'),
            ],
        ];

        // Save report to file
        $reportPath = storage_path('app/backups/reports');
        if (!file_exists($reportPath)) {
            mkdir($reportPath, 0755, true);
        }

        $reportFile = $reportPath . "/backup_report_{$sessionId}.json";
        file_put_contents($reportFile, json_encode($report, JSON_PRETTY_PRINT));

        // Display summary
        $this->info('');
        $this->info('📋 === BACKUP SUMMARY ===');
        $this->info("Session ID: {$sessionId}");
        $this->info("Duration: {$duration} seconds");
        $this->info("Database: " . ($results['database'] ? '✅ SUCCESS' : '❌ FAILED'));
        $this->info("Files: " . ($results['files'] ? '✅ SUCCESS' : '❌ FAILED'));
        $this->info("Overall: " . ($report['overall_status'] === 'SUCCESS' ? '✅ SUCCESS' : '⚠️ ' . $report['overall_status']));
    }

    /**
     * Send backup notification.
     */
    protected function sendNotification(array $results, float $startTime): void
    {
        $adminEmail = config('mail.admin_email');
        if (!$adminEmail) {
            $this->warn('⚠️ Admin email not configured, skipping notification');
            return;
        }

        try {
            $duration = round(microtime(true) - $startTime, 2);
            $status = ($results['database'] && $results['files']) ? 'success' : 'partial';
            
            Mail::to($adminEmail)->send(new BackupNotification(
                'Full Backup',
                0, // Size will be calculated in the mail class
                $duration,
                $status,
                $results
            ));
            
            $this->info('📧 Notification email sent successfully');
            
        } catch (\Exception $e) {
            $this->error('❌ Failed to send notification: ' . $e->getMessage());
        }
    }
}
