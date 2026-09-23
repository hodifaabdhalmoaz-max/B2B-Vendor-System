<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAttribute extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'product_id',
        'attribute_id',
        'attribute_value_id',
        'price_adjustment',
        'quantity'
    ];
    
    protected $casts = [
        'price_adjustment' => 'decimal:2',
        'quantity' => 'integer',
    ];
    
    /**
     * Get the product that owns the product attribute.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    /**
     * Get the attribute that owns the product attribute.
     */
    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }
    
    /**
     * Get the attribute value that owns the product attribute.
     */
    public function attributeValue()
    {
        return $this->belongsTo(AttributeValue::class);
    }
}
