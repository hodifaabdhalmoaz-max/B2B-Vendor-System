<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'featured',
        'status',
        'slug',
        'image',
        'icon',
        'parent_id',
        'storefront_contexts',
        'storefront_order',
        'show_in_home_tabs',
        'home_tab_order',
        'home_tab_label',
    ];

    protected function casts(): array
    {
        return [
            'featured' => 'boolean',
            'parent_id' => 'integer',
            'storefront_contexts' => 'array',
            'storefront_order' => 'integer',
            'show_in_home_tabs' => 'boolean',
            'home_tab_order' => 'integer',
        ];
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function assignedProducts()
    {
        return $this->belongsToMany(Product::class, 'category_product')
            ->withTimestamps();
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')
            ->orderBy('storefront_order')
            ->orderBy('name');
    }

    public function scopeActive($query)
    {
        return $query->where(function ($query) {
            $query->where('status', 'active')->orWhereNull('status');
        });
    }

    public function scopeHomeTabs($query)
    {
        return $query->active()
            ->where('show_in_home_tabs', true)
            ->orderBy('home_tab_order')
            ->orderBy('name');
    }

    public function getHomeTabDisplayLabelAttribute(): string
    {
        return $this->home_tab_label ?: $this->name;
    }
}
