<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignCreative extends Model
{
    use HasFactory;

    public const LINK_TYPES = [
        'url',
        'product',
        'category',
        'coupon',
        'collection',
    ];

    protected $fillable = [
        'campaign_id',
        'title',
        'subtitle',
        'description',
        'cta_text',
        'link_type',
        'link_url',
        'product_id',
        'category_id',
        'background_color',
        'text_color',
        'alt_text',
    ];

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

    public function resolvedUrl(): string
    {
        return match ($this->link_type) {
            'product' => $this->product ? route('shop.product.details', ['product_slug' => $this->product->slug]) : route('shop.index'),
            'category' => $this->category ? route('shop.category', $this->category->slug) : route('shop.index'),
            default => $this->link_url ?: route('shop.index'),
        };
    }
}
