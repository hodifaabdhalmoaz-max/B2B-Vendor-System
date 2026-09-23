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
        'stock_delta',
        'reserved_delta',
        'stock_on_hand_after',
        'reserved_quantity_after',
        'request_fingerprint',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'stock_delta' => 'integer',
            'reserved_delta' => 'integer',
            'stock_on_hand_after' => 'integer',
            'reserved_quantity_after' => 'integer',
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (InventoryMovement $inventoryMovement): void {
            if ($inventoryMovement->exists) {
                throw new InvalidArgumentException('Inventory movements are append-only.');
            }

            if ((int) $inventoryMovement->quantity <= 0) {
                throw new InvalidArgumentException('Inventory movement quantity must be positive.');
            }
        });

        static::deleting(function (): void {
            throw new InvalidArgumentException('Inventory movements are append-only.');
        });
    }

    public function save(array $options = [])
    {
        // Instance-level quiet writes bypass model events, so guard here too.
        if ($this->exists) {
            throw new InvalidArgumentException('Inventory movements are append-only.');
        }

        return parent::save($options);
    }

    public function delete()
    {
        throw new InvalidArgumentException('Inventory movements are append-only.');
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
