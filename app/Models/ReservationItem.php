<?php

namespace App\Models;

use App\Support\DecimalMoney;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class ReservationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'product_variant_id',
        'quantity',
        'unit_price',
        'product_name_snapshot',
        'sku_snapshot',
        'variant_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'variant_snapshot' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (ReservationItem $reservationItem): void {
            if ((int) $reservationItem->quantity <= 0) {
                throw new InvalidArgumentException('Reservation item quantity must be positive.');
            }
            if ($reservationItem->exists && $reservationItem->isDirty()) {
                throw new InvalidArgumentException('Reservation item snapshots are immutable.');
            }
        });

        static::deleting(function (): void {
            throw new InvalidArgumentException('Reservation item snapshots are immutable.');
        });
    }

    public function save(array $options = [])
    {
        if ($this->exists && $this->isDirty()) {
            throw new InvalidArgumentException('Reservation item snapshots are immutable.');
        }

        return parent::save($options);
    }

    public function delete()
    {
        throw new InvalidArgumentException('Reservation item snapshots are immutable.');
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function getLineTotalAttribute(): string
    {
        $cents = DecimalMoney::toCents($this->unit_price);
        $quantity = (int) $this->quantity;
        if ($cents > 0 && $quantity > intdiv(PHP_INT_MAX, $cents)) {
            throw new \OverflowException('Reservation item total exceeds the supported range.');
        }

        return DecimalMoney::formatCents($cents * $quantity);
    }
}
