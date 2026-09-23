<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'color_id',
        'size_id',
        'sku',
        'price_adjustment',
        'is_active',
    ];

    protected static function booted(): void
    {
        static::saving(function (ProductVariant $variant): void {
            $variant->variant_key = self::buildVariantKey($variant->color_id, $variant->size_id);
        });
    }

    protected function casts(): array
    {
        return [
            'price_adjustment' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public static function buildVariantKey(?int $colorId, ?int $sizeId): string
    {
        return 'C:'.((int) ($colorId ?: 0)).'|S:'.((int) ($sizeId ?: 0));
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    public function size()
    {
        return $this->belongsTo(Size::class);
    }

    public function inventoryItem()
    {
        return $this->hasOne(InventoryItem::class);
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function reservationItems()
    {
        return $this->hasMany(ReservationItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function effectivePrice(): string
    {
        $basePrice = $this->product?->current_price ?? 0;

        return number_format(max(0, (float) $basePrice + (float) $this->price_adjustment), 2, '.', '');
    }
}
