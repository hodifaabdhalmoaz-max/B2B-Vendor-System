<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class BackupNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $backupType;
    public $fileSize;
    public $duration;
    public $status;
    public $results;
    public $timestamp;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $backupType,
        int $fileSize,
        float $duration,
        string $status = 'success',
        array $results = []
    ) {
        $this->backupType = $backupType;
        $this->fileSize = $fileSize;
        $this->duration = $duration;
        $this->status = $status;
        $this->results = $results;
        $this->timestamp = Carbon::now();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->getSubject();
        
        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.backup-notification',
            with: [
                'backupType' => $this->backupType,
                'fileSize' => $this->formatBytes($this->fileSize),
                'duration' => $this->duration,
                'status' => $this->status,
                'results' => $this->results,
                'timestamp' => $this->timestamp->format('Y-m-d H:i:s'),
                'statusIcon' => $this->getStatusIcon(),
                'statusColor' => $this->getStatusColor(),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Get email subject based on status.
     */
    protected function getSubject(): string
    {
        $appName = config('app.name');
        $statusText = ucfirst($this->status);
        
        return "[{$appName}] {$this->backupType} Backup {$statusText}";
    }

    /**
     * Get status icon.
     */
    protected function getStatusIcon(): string
    {
        return match($this->status) {
            'success' => '✅',
            'partial' => '⚠️',
            'failed' => '❌',
            default => 'ℹ️',
        };
    }

    /**
     * Get status color for styling.
     */
    protected function getStatusColor(): string
    {
        return match($this->status) {
            'success' => '#28a745',
            'partial' => '#ffc107',
            'failed' => '#dc3545',
            default => '#17a2b8',
        };
    }

    /**
     * Format bytes to human readable format.
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        if ($bytes === 0) {
            return 'N/A';
        }
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
