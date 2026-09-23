<?php

// app/Jobs/GenerateInvoice.php
// Queue-based invoice PDF generation to offload heavy operations
// from the request cycle and prevent slow responses that contribute to 429s.

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateInvoice implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    /**
     * Maximum retry attempts.
     */
    public int $tries = 3;

    /**
     * Job timeout in seconds.
     */
    public int $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly Order $order,
    ) {
        // Process on the 'invoices' queue
        $this->onQueue('invoices');
    }

    /**
     * Execute the job — generate the invoice HTML and save it.
     */
    public function handle(): void
    {
        try {
            // Load all relationships needed for the invoice
            $this->order->load([
                'user',
                'orderItems.product',
            ]);

            // Generate the invoice HTML content
            $invoiceHtml = view('emails.invoice', [
                'order' => $this->order,
            ])->render();

            // Define the storage path for the invoice
            $filename = "invoices/invoice_{$this->order->id}_{$this->order->created_at->format('Ymd')}.html";

            // Save the invoice to storage
            Storage::disk('local')->put($filename, $invoiceHtml);

            // Update the order record with the invoice path
            $this->order->update([
                'invoice_path' => $filename,
            ]);

            Log::info('Invoice generated successfully', [
                'order_id' => $this->order->id,
                'user_id' => $this->order->user_id,
                'path' => $filename,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to generate invoice', [
                'order_id' => $this->order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('GenerateInvoice job failed permanently', [
            'order_id' => $this->order->id,
            'user_id' => $this->order->user_id,
            'error' => $exception->getMessage(),
        ]);
    }
}
