<?php

namespace App\Listeners;

use App\Events\ProductViewed;
use App\Services\StatisticService;
use App\Services\AuditService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogProductView implements ShouldQueue
{
    use InteractsWithQueue;

    protected $statisticService;
    protected $auditService;

    /**
     * Create the event listener.
     */
    public function __construct(StatisticService $statisticService, AuditService $auditService)
    {
        $this->statisticService = $statisticService;
        $this->auditService = $auditService;
    }

    /**
     * Handle the event.
     */
    public function handle(ProductViewed $event): void
    {
        try {
            // Log product view statistic
            $this->statisticService->logProductView($event->product->id);

            // Log to audit trail
            $this->auditService->log('product_view', [
                'product_id' => $event->product->id,
                'product_name' => $event->product->name,
                'product_price' => $event->product->current_price,
                'user_id' => $event->user?->id,
                'ip_address' => $event->ipAddress,
                'user_agent' => $event->userAgent,
            ]);

            // Increment product views count
            $event->product->increment('views');

        } catch (\Exception $e) {
            Log::error('Failed to log product view', [
                'product_id' => $event->product->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(ProductViewed $event, \Throwable $exception): void
    {
        Log::error('LogProductView listener failed', [
            'product_id' => $event->product->id,
            'error' => $exception->getMessage()
        ]);
    }
}
