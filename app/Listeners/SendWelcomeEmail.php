<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Jobs\SendWelcomeEmailJob;
use App\Services\AuditService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendWelcomeEmail implements ShouldQueue
{
    use InteractsWithQueue;

    protected $auditService;

    /**
     * Create the event listener.
     */
    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Handle the event.
     */
    public function handle(UserRegistered $event): void
    {
        try {
            // Dispatch welcome email job
            SendWelcomeEmailJob::dispatch($event->user);

            // Log user registration
            $this->auditService->log('user_registered', [
                'user_id' => $event->user->id,
                'email' => $event->user->email,
                'ip_address' => $event->ipAddress,
                'user_agent' => $event->userAgent,
            ]);

            Log::info('Welcome email queued for new user', [
                'user_id' => $event->user->id,
                'email' => $event->user->email,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send welcome email', [
                'user_id' => $event->user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(UserRegistered $event, \Throwable $exception): void
    {
        Log::error('SendWelcomeEmail listener failed', [
            'user_id' => $event->user->id,
            'error' => $exception->getMessage()
        ]);
    }
}
