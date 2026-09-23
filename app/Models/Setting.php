<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'key',
        'value',
        'display_name',
        'description',
        'type',
        'options',
        'group',
        'order',
        'is_translatable'
    ];
    
    protected $casts = [
        'options' => 'array',
        'order' => 'integer',
        'is_translatable' => 'boolean',
    ];
    
    /**
     * Scope a query to only include settings of a specific group.
     */
    public function scopeInGroup($query, $group)
    {
        return $query->where('group', $group);
    }
    
    /**
     * Scope a query to order by the order field.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }
    
    /**
     * Get a setting value by key.
     */
    public static function get($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        
        if ($setting) {
            return $setting->value;
        }
        
        return $default;
    }
    
    /**
     * Set a setting value by key.
     */
    public static function set($key, $value)
    {
        $setting = self::where('key', $key)->first();
        
        if ($setting) {
            $setting->value = $value;
            $setting->save();
            
            return $setting;
        }
        
        return false;
    }
}
