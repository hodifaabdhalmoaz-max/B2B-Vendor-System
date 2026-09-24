<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use App\Services\ReservationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class ExpireReservations extends Command
{
    protected $signature = 'reservations:expire';

    protected $description = 'Release due pending B2B reservations';

    public function handle(ReservationService $service): int
    {
        $expired = 0;
        $failed = 0;
        $cutoff = now();

        Reservation::query()
            ->where('status', Reservation::STATUS_PENDING_REVIEW)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $cutoff)
            ->whereNull('released_at')
            ->orderBy('id')
            ->chunkById(100, function ($reservations) use ($service, &$expired, &$failed): void {
                foreach ($reservations as $reservation) {
                    try {
                        if ($service->expire($reservation)) {
                            $expired++;
                        }
                    } catch (Throwable $exception) {
                        $failed++;
                        Log::error('Reservation expiration failed', [
                            'reservation_id' => $reservation->id,
                            'exception' => $exception,
                        ]);
                    }
                }
            });

        $this->info("Expired {$expired} reservations; {$failed} failed.");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
