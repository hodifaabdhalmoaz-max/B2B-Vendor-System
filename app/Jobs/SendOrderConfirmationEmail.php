<?php

namespace App\Jobs;

use App\Models\Order;
use App\Mail\OrderConfirmationEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendOrderConfirmationEmail implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $order;
    public $tries = 3;
    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->onQueue('emails');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Load order relationships
            $this->order->load(['user', 'orderItems.product']);

            Mail::to($this->order->user->email)->send(new OrderConfirmationEmail($this->order));

            Log::info('Order confirmation email sent successfully', [
                'order_id' => $this->order->id,
                'user_id' => $this->order->user_id,
                'email' => $this->order->user->email,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send order confirmation email', [
                'order_id' => $this->order->id,
                'user_id' => $this->order->user_id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SendOrderConfirmationEmail failed permanently', [
            'order_id' => $this->order->id,
            'user_id' => $this->order->user_id,
            'error' => $exception->getMessage(),
        ]);
    }
}
