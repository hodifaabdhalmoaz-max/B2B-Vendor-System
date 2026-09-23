<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingAddress extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'order_id',
        'name',
        'phone',
        'locality',
        'address',
        'city',
        'state',
        'country',
        'landmark',
        'zip',
        'type'
    ];
    
    /**
     * Get the order that owns the shipping address.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    
    /**
     * Get the full address.
     */
    public function getFullAddressAttribute()
    {
        return "{$this->address}, {$this->locality}, {$this->city}, {$this->state}, {$this->country} - {$this->zip}";
    }
}
