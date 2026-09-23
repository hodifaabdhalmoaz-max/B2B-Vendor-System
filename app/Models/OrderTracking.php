<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderTracking extends Model
{
    use HasFactory;
    
    protected $table = 'order_tracking';
    
    protected $fillable = [
        'order_id',
        'status',
        'comment',
        'location',
        'updated_by'
    ];
    
    /**
     * Get the order that owns the tracking.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    
    /**
     * Get the user that updated the tracking.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
