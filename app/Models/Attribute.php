<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'code',
        'frontend_type',
        'is_filterable',
        'is_required'
    ];
    
    protected $casts = [
        'is_filterable' => 'boolean',
        'is_required' => 'boolean',
    ];
    
    /**
     * Get the values for the attribute.
     */
    public function values()
    {
        return $this->hasMany(AttributeValue::class);
    }
    
    /**
     * Get the product attributes for the attribute.
     */
    public function productAttributes()
    {
        return $this->hasMany(ProductAttribute::class);
    }
}
