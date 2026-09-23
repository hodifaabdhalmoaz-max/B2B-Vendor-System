<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'ip_address',
        'user_agent',
        'successful',
        'failure_reason',
        'request_data',
        'country',
        'city',
        'attempted_at',
    ];

    protected $casts = [
        'successful' => 'boolean',
        'request_data' => 'array',
        'attempted_at' => 'datetime',
    ];

    /**
     * Scope for failed attempts.
     */
    public function scopeFailed($query)
    {
        return $query->where('successful', false);
    }

    /**
     * Scope for successful attempts.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('successful', true);
    }

    /**
     * Scope for recent attempts.
     */
    public function scopeRecent($query, $minutes = 60)
    {
        return $query->where('attempted_at', '>=', now()->subMinutes($minutes));
    }

    /**
     * Scope for specific IP.
     */
    public function scopeFromIp($query, string $ip)
    {
        return $query->where('ip_address', $ip);
    }

    /**
     * Scope for specific email.
     */
    public function scopeForEmail($query, string $email)
    {
        return $query->where('email', $email);
    }

    /**
     * Log a login attempt.
     */
    public static function logAttempt(
        ?string $email,
        bool $successful,
        ?string $failureReason = null,
        array $requestData = []
    ): self {
        return self::create([
            'email' => $email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'successful' => $successful,
            'failure_reason' => $failureReason,
            'request_data' => $requestData,
            'country' => self::getCountryFromIp(request()->ip()),
            'city' => self::getCityFromIp(request()->ip()),
            'attempted_at' => now(),
        ]);
    }

    /**
     * Get country from IP (simplified).
     */
    protected static function getCountryFromIp(string $ip): ?string
    {
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return 'Local';
        }
        
        return 'Unknown';
    }

    /**
     * Get city from IP (simplified).
     */
    protected static function getCityFromIp(string $ip): ?string
    {
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return 'Local';
        }
        
        return 'Unknown';
    }

    /**
     * Get failed attempts count for IP in time period.
     */
    public static function getFailedAttemptsForIp(string $ip, int $minutes = 15): int
    {
        return self::failed()
            ->fromIp($ip)
            ->recent($minutes)
            ->count();
    }

    /**
     * Get failed attempts count for email in time period.
     */
    public static function getFailedAttemptsForEmail(string $email, int $minutes = 30): int
    {
        return self::failed()
            ->forEmail($email)
            ->recent($minutes)
            ->count();
    }
}
