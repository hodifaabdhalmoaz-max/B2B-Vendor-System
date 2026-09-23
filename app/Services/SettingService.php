<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    /**
     * Get all settings.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all()
    {
        return Cache::remember('settings.all', 60 * 24, function () {
            return Setting::all();
        });
    }
    
    /**
     * Get settings by group.
     *
     * @param string $group
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByGroup($group)
    {
        return Cache::remember("settings.group.{$group}", 60 * 24, function () use ($group) {
            return Setting::inGroup($group)->ordered()->get();
        });
    }
    
    /**
     * Get a setting value by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return Cache::remember("settings.key.{$key}", 60 * 24, function () use ($key, $default) {
            return Setting::get($key, $default);
        });
    }
    
    /**
     * Set a setting value by key.
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function set($key, $value)
    {
        $result = Setting::set($key, $value);
        
        if ($result) {
            Cache::forget("settings.key.{$key}");
            Cache::forget('settings.all');
            Cache::forget("settings.group.{$result->group}");
        }
        
        return $result;
    }
    
    /**
     * Clear settings cache.
     *
     * @return void
     */
    public function clearCache()
    {
        Cache::forget('settings.all');
        
        $groups = Setting::select('group')->distinct()->pluck('group');
        
        foreach ($groups as $group) {
            Cache::forget("settings.group.{$group}");
        }
        
        $keys = Setting::pluck('key');
        
        foreach ($keys as $key) {
            Cache::forget("settings.key.{$key}");
        }
    }
}
