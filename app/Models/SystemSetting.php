<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $table = 'system_settings';
    protected $primaryKey = 'setting_id';
    
    protected $fillable = [
        'setting_key',
        'setting_value',
        'description',
    ];
    
    /**
     * Get setting value by key
     */
    public static function get($key, $default = null)
    {
        $setting = self::where('setting_key', $key)->first();
        return $setting ? $setting->setting_value : $default;
    }
    
    /**
     * Set setting value by key
     */
    public static function set($key, $value, $description = null)
    {
        return self::updateOrCreate(
            ['setting_key' => $key],
            [
                'setting_value' => $value,
                'description' => $description ?? "Setting for {$key}"
            ]
        );
    }
    
    /**
     * Check if system is open
     */
    public static function isSystemOpen()
    {
        return self::get('system_status', 'open') === 'open';
    }
}
