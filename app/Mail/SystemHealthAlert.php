<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class SystemHealthAlert extends Mailable
{
    use Queueable, SerializesModels;

    public $healthStatus;
    public $criticalIssues;
    public $warnings;

    /**
     * Create a new message instance.
     */
    public function __construct(array $healthStatus, array $criticalIssues, array $warnings)
    {
        $this->healthStatus = $healthStatus;
        $this->criticalIssues = $criticalIssues;
        $this->warnings = $warnings;
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
            view: 'emails.system-health-alert',
            with: [
                'healthStatus' => $this->healthStatus,
                'criticalIssues' => $this->criticalIssues,
                'warnings' => $this->warnings,
                'timestamp' => $this->healthStatus['timestamp']->format('Y-m-d H:i:s'),
                'statusIcon' => $this->getStatusIcon(),
                'statusColor' => $this->getStatusColor(),
                'priority' => $this->getPriority(),
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
        $status = $this->healthStatus['overall_status'];
        $priority = $this->getPriority();
        
        return "[{$appName}] {$priority} System Health Alert - " . ucfirst($status);
    }

    /**
     * Get status icon.
     */
    protected function getStatusIcon(): string
    {
        return match($this->healthStatus['overall_status']) {
            'healthy' => '✅',
            'warning' => '⚠️',
            'critical' => '🚨',
            default => 'ℹ️',
        };
    }

    /**
     * Get status color for styling.
     */
    protected function getStatusColor(): string
    {
        return match($this->healthStatus['overall_status']) {
            'healthy' => '#28a745',
            'warning' => '#ffc107',
            'critical' => '#dc3545',
            default => '#17a2b8',
        };
    }

    /**
     * Get priority level.
     */
    protected function getPriority(): string
    {
        return match($this->healthStatus['overall_status']) {
            'critical' => 'URGENT',
            'warning' => 'WARNING',
            'healthy' => 'INFO',
            default => 'NOTICE',
        };
    }
}
