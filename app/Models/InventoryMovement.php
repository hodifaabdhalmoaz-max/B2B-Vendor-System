<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class InventoryMovement extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    public const TYPE_STOCK_IN = 'stock_in';

    public const TYPE_STOCK_OUT = 'stock_out';

    public const TYPE_RESERVE = 'reserve';

    public const TYPE_RELEASE = 'release';

    public const TYPE_ADJUSTMENT = 'adjustment';

    public const TYPE_ORDER_COMPLETED = 'order_completed';

    protected $fillable = [
        'product_variant_id',
        'type',
        'quantity',
        'reference_type',
        'reference_id',
        'actor_user_id',
        'idempotency_key',
        'metadata',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (InventoryMovement $inventoryMovement): void {
            if ((int) $inventoryMovement->quantity <= 0) {
                throw new InvalidArgumentException('Inventory movement quantity must be positive.');
            }
        });
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
