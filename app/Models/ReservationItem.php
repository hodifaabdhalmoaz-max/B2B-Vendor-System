<?php

namespace App\Models;

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
        });
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
        return number_format((float) $this->unit_price * (int) $this->quantity, 2, '.', '');
    }
}
