<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttributeValue extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'attribute_id',
        'value',
        'code'
    ];
    
    /**
     * Get the attribute that owns the value.
     */
    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }
    
    /**
     * Get the product attributes for the attribute value.
     */
    public function productAttributes()
    {
        return $this->hasMany(ProductAttribute::class);
    }
}
