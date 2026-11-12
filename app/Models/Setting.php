<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Key/value site settings editable in Admin > Settings.
 * Read with the setting('key', 'default') helper.
 */
class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function all_cached(): array
    {
        return Cache::remember('settings.all', 3600, function () {
            try {
                return static::query()->pluck('value', 'key')->all();
            } catch (\Throwable) {
                return [];
            }
        });
    }

    public static function put(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('settings.all');
    }
}
