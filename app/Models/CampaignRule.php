<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignRule extends Model
{
    use HasFactory;

    public const TARGET_TYPES = [
        'all',
        'home_tab',
        'category',
        'product',
        'device',
        'guest_only',
        'logged_in_only',
    ];

    public const OPERATORS = [
        'equals',
        'in',
        'greater_than',
        'less_than',
    ];

    protected $fillable = [
        'campaign_id',
        'target_type',
        'operator',
        'value',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}
