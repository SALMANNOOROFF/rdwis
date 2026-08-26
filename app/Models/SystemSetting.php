<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $table = 'cen.system_settings';
    protected $primaryKey = 'set_key';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'set_key',
        'set_value',
        'set_description'
    ];

    /**
     * Get setting value by key with optional default fallback
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        try {
            $record = static::find($key);
            return $record ? $record->set_value : $default;
        } catch (\Exception $e) {
            return $default;
        }
    }

    /**
     * Set or update setting value by key
     */
    public static function set(string $key, mixed $value, ?string $description = null): void
    {
        static::updateOrCreate(
            ['set_key' => $key],
            [
                'set_value' => (string) $value,
                'set_description' => $description ?? static::find($key)?->set_description
            ]
        );
    }
}
