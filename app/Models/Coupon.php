<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'value',
        'cart_value',
        'expiry_date',
        'is_active',
        'used_count',
        'usage_limit',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'cart_value' => 'decimal:2',
            'expiry_date' => 'date',
            'is_active' => 'boolean',
            'used_count' => 'integer',
            'usage_limit' => 'integer',
        ];
    }
}
