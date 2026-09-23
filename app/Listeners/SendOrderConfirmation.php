<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Jobs\SendOrderConfirmationEmail;
use App\Services\AuditService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendOrderConfirmation implements ShouldQueue
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
    public function handle(OrderCreated $event): void
    {
        try {
            // Dispatch email job
            SendOrderConfirmationEmail::dispatch($event->order);

            // Log order creation
            $this->auditService->log('order_created', [
                'order_id' => $event->order->id,
                'user_id' => $event->order->user_id,
                'total' => $event->order->total,
                'status' => $event->order->status,
            ]);

            Log::info('Order confirmation email queued', [
                'order_id' => $event->order->id,
                'user_id' => $event->order->user_id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send order confirmation', [
                'order_id' => $event->order->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(OrderCreated $event, \Throwable $exception): void
    {
        Log::error('SendOrderConfirmation listener failed', [
            'order_id' => $event->order->id,
            'error' => $exception->getMessage()
        ]);
    }
}
