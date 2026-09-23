<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CampaignAsset extends Model
{
    use HasFactory;

    public const ROLES = [
        'background',
        'hero_product',
        'promo_card',
        'circle_item',
        'mobile_banner',
        'desktop_banner',
    ];

    protected $fillable = [
        'campaign_id',
        'role',
        'image_path',
        'title',
        'subtitle',
        'price_text',
        'link_url',
        'product_id',
        'category_id',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function imageUrl(): string
    {
        if (is_file(public_path('storage/'.$this->image_path))) {
            return Storage::disk('public')->url($this->image_path);
        }

        return route('campaign-assets.image', $this);
    }

    public function resolvedUrl(): string
    {
        if ($this->product) {
            return route('shop.product.details', ['product_slug' => $this->product->slug]);
        }

        if ($this->category) {
            return route('shop.category', $this->category->slug);
        }

        return $this->link_url ?: route('shop.index');
    }
}
