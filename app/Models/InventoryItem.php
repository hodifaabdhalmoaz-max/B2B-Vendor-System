<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_variant_id',
        'stock_on_hand',
        'reserved_quantity',
        'low_stock_threshold',
    ];

    protected function casts(): array
    {
        return [
            'stock_on_hand' => 'integer',
            'reserved_quantity' => 'integer',
            'low_stock_threshold' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (InventoryItem $inventoryItem): void {
            if ((int) $inventoryItem->stock_on_hand < 0 || (int) $inventoryItem->reserved_quantity < 0) {
                throw new InvalidArgumentException('Inventory quantities cannot be negative.');
            }

            if ((int) $inventoryItem->reserved_quantity > (int) $inventoryItem->stock_on_hand) {
                throw new InvalidArgumentException('Reserved quantity cannot exceed stock on hand.');
            }
        });
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function getAvailableQuantityAttribute(): int
    {
        return $this->availableQuantity();
    }

    public function availableQuantity(): int
    {
        return (int) $this->stock_on_hand - (int) $this->reserved_quantity;
    }

    public function canReserve(int $quantity): bool
    {
        return $quantity > 0 && $quantity <= $this->availableQuantity();
    }
}
