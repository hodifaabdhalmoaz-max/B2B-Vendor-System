<?php

namespace App\Models;

use App\Support\DecimalMoney;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    public const STATUS_PENDING_REVIEW = 'pending_review';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_PREPARING = 'preparing';

    public const STATUS_SHIPPED = 'shipped';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'reservation_number',
        'idempotency_key',
        'request_fingerprint',
        'reseller_profile_id',
        'status',
        'expires_at',
        'confirmed_at',
        'cancelled_at',
        'released_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'released_at' => 'datetime',
        ];
    }

    public function resellerProfile()
    {
        return $this->belongsTo(ResellerProfile::class);
    }

    public function reservationItems()
    {
        return $this->hasMany(ReservationItem::class);
    }

    public function getTotalAttribute(): string
    {
        $items = $this->relationLoaded('reservationItems') ? $this->reservationItems : $this->reservationItems()->get();
        $cents = 0;

        foreach ($items as $item) {
            $line = DecimalMoney::toCents($item->line_total);
            if ($line > PHP_INT_MAX - $cents) {
                throw new \OverflowException('Reservation total exceeds the supported range.');
            }
            $cents += $line;
        }

        return DecimalMoney::formatCents($cents);
    }
}
