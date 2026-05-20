<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type', // string, boolean, integer, array
        'group',
        'description',
    ];

    /**
     * Get setting value by key statically with default support and type casting
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        switch ($setting->type) {
            case 'boolean':
                return filter_var($setting->value, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
                return intval($setting->value);
            case 'array':
                return json_decode($setting->value, true) ?: [];
            default:
                return $setting->value;
        }
    }

    /**
     * Set setting value statically
     */
    public static function setValue(string $key, $value, string $type = 'string', string $group = 'general', string $description = null)
    {
        if (is_array($value)) {
            $valStr = json_encode($value);
            $type = 'array';
        } elseif (is_bool($value)) {
            $valStr = $value ? 'true' : 'false';
            $type = 'boolean';
        } else {
            $valStr = (string)$value;
        }

        return self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $valStr,
                'type' => $type,
                'group' => $group,
                'description' => $description,
            ]
        );
    }
}
