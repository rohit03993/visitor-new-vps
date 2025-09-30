<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Check if settings table exists
     */
    protected static function tableExists()
    {
        try {
            return Schema::hasTable('settings');
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get a setting value by key (SAFE - won't crash if table doesn't exist)
     */
    public static function get($key, $default = null)
    {
        // If table doesn't exist, return default immediately
        if (!self::tableExists()) {
            return $default;
        }

        try {
            return Cache::remember("setting_{$key}", 3600, function () use ($key, $default) {
                $setting = self::where('key', $key)->first();
                return $setting ? $setting->value : $default;
            });
        } catch (\Exception $e) {
            // If any error, return default
            return $default;
        }
    }

    /**
     * Set a setting value (SAFE - won't crash if table doesn't exist)
     */
    public static function set($key, $value)
    {
        // If table doesn't exist, do nothing
        if (!self::tableExists()) {
            return null;
        }

        try {
            $setting = self::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
            
            Cache::forget("setting_{$key}");
            
            return $setting;
        } catch (\Exception $e) {
            \Log::error('Setting update failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get the app logo path (SAFE - always returns valid logo)
     */
    public static function logo()
    {
        $logoPath = self::get('app_logo', 'images/logos/default-logo.svg');
        return asset($logoPath);
    }
}
