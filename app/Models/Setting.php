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

    /** Per-request copy: text() and setting() run hundreds of times per page, and each Cache::remember is a database query when the cache store is the database. */
    private static ?\WeakMap $memo = null;

    public static function all_cached(): array
    {
        $load = fn () => Cache::remember('settings.all', 3600, function () {
            try {
                return static::query()->pluck('value', 'key')->all();
            } catch (\Throwable) {
                return [];
            }
        });
        if (app()->runningInConsole()) {   // queue workers and commands outlive a request: always read through the cache
            return $load();
        }
        self::$memo ??= new \WeakMap();

        return self::$memo[app('request')] ??= $load();
    }

    public static function put(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('settings.all');
        self::$memo = null;
    }
}
