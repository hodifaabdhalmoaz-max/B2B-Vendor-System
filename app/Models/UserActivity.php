<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class UserActivity extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'action',
        'ip_address',
        'user_agent',
        'details'
    ];
    
    protected $casts = [
        'details' => 'array',
    ];
    
    /**
     * Get the user that owns the activity.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Scope a query to only include activities of a specific action.
     */
    public function scopeOfAction($query, $action)
    {
        return $query->where('action', $action);
    }
    
    /**
     * Scope a query to only include activities from a specific date range.
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
    
    /**
     * Log a user activity.
     *
     * Fail-safe: audit logging should never crash core business operations.
     * If the user_activities table is missing or a DB error occurs,
     * the activity is recorded in the application log file instead.
     */
    public static function log($userId, $action, $details = null)
    {
        try {
            return self::create([
                'user_id' => $userId,
                'action' => $action,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'details' => $details,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::warning('UserActivity::log failed — falling back to file log.', [
                'error'   => $e->getMessage(),
                'user_id' => $userId,
                'action'  => $action,
                'details' => $details,
            ]);

            return null;
        }
    }
}
