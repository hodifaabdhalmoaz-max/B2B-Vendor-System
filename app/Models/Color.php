<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'hex_code',
        'description',
        'is_active',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get the products for the color.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_colors')
            ->withPivot('quantity', 'price_adjustment', 'image')
            ->withTimestamps();
    }

    public function productImages()
    {
        return $this->hasMany(ProductColorImage::class);
    }

    /**
     * Scope a query to only include active colors.
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
}
