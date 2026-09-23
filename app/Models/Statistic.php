<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Statistic extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'type',
        'reference_id',
        'reference_type',
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
        'data'
    ];
    
    protected $casts = [
        'data' => 'array',
    ];
    
    /**
     * Get the user that owns the statistic.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the reference model (polymorphic).
     */
    public function reference()
    {
        return $this->morphTo();
    }
    
    /**
     * Scope a query to filter by type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
    
    /**
     * Scope a query to filter by date range.
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}
