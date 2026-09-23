<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    public const TYPES = [
        'banner',
        'home_tab_banner',
        'discount',
        'new_arrival',
        'flash_sale',
        'coupon',
        'announcement',
    ];

    public const STATUSES = [
        'draft',
        'scheduled',
        'active',
        'paused',
        'expired',
    ];

    protected $fillable = [
        'name',
        'slug',
        'type',
        'status',
        'starts_at',
        'ends_at',
        'priority',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'priority' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function creative()
    {
        return $this->hasOne(CampaignCreative::class);
    }

    public function assets()
    {
        return $this->hasMany(CampaignAsset::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function rules()
    {
        return $this->hasMany(CampaignRule::class);
    }

    public function placements()
    {
        return $this->belongsToMany(AdPlacement::class, 'campaign_placement')
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    public function scopeCurrentlyActive(Builder $query): Builder
    {
        $now = now();

        return $query
            ->where('campaigns.is_active', true)
            ->where('campaigns.status', 'active')
            ->where(function (Builder $query) use ($now) {
                $query->whereNull('campaigns.starts_at')->orWhere('campaigns.starts_at', '<=', $now);
            })
            ->where(function (Builder $query) use ($now) {
                $query->whereNull('campaigns.ends_at')->orWhere('campaigns.ends_at', '>=', $now);
            });
    }
}
