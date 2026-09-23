<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingMethod extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'code',
        'description',
        'cost',
        'minimum_order_amount',
        'is_active',
        'order'
    ];
    
    protected $casts = [
        'cost' => 'decimal:2',
        'minimum_order_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'order' => 'integer',
    ];
    
    /**
     * Get the orders for the shipping method.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    
    /**
     * Scope a query to only include active shipping methods.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    /**
     * Scope a query to order by the order field.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }
    
    /**
     * Get the formatted cost.
     */
    public function getFormattedCostAttribute()
    {
        return number_format($this->cost, 2);
    }
    
    /**
     * Get the formatted minimum order amount.
     */
    public function getFormattedMinimumOrderAmountAttribute()
    {
        return number_format($this->minimum_order_amount, 2);
    }
}
