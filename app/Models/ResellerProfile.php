<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResellerProfile extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'user_id',
        'business_name',
        'whatsapp',
        'governorate',
        'group_name',
        'notes',
        'status',
        'reservation_enabled',
        'reservation_timeout_minutes',
    ];

    protected function casts(): array
    {
        return [
            'reservation_enabled' => 'boolean',
            'reservation_timeout_minutes' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
