<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_type',
        'ip_address',
        'user_agent',
        'event_data',
        'risk_level',
        'blocked',
        'location',
        'occurred_at',
    ];

    protected $casts = [
        'event_data' => 'array',
        'blocked' => 'boolean',
        'occurred_at' => 'datetime',
    ];

    /**
     * Get the user that owns the security log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for high risk events.
     */
    public function scopeHighRisk($query)
    {
        return $query->whereIn('risk_level', ['high', 'critical']);
    }

    /**
     * Scope for blocked events.
     */
    public function scopeBlocked($query)
    {
        return $query->where('blocked', true);
    }

    /**
     * Scope for recent events.
     */
    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('occurred_at', '>=', now()->subHours($hours));
    }

    /**
     * Create a security log entry.
     */
    public static function logEvent(
        string $eventType,
        ?int $userId = null,
        array $eventData = [],
        string $riskLevel = 'low',
        bool $blocked = false
    ): self {
        return self::create([
            'user_id' => $userId,
            'event_type' => $eventType,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'event_data' => $eventData,
            'risk_level' => $riskLevel,
            'blocked' => $blocked,
            'location' => self::getLocationFromIp(request()->ip()),
            'occurred_at' => now(),
        ]);
    }

    /**
     * Get location from IP address (simplified).
     */
    protected static function getLocationFromIp(string $ip): ?string
    {
        // في بيئة الإنتاج، يمكن استخدام خدمة GeoIP
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return 'Local';
        }
        
        return 'Unknown';
    }
}
