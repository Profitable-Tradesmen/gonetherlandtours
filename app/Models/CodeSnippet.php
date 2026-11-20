<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * A custom code snippet (script, style, tracking pixel, any HTML) managed in Admin > Header & Footer.
 * The code is output EXACTLY as entered, in the chosen place, only on the chosen pages. Administrators only.
 */
class CodeSnippet extends Model
{
    protected $guarded = [];

    public const POSITIONS = [
        'head' => 'In the <head>',
        'body_start' => 'Start of the <body> (right after the opening tag)',
        'body_end' => 'End of the <body> (before the closing tag)',
    ];

    private const CACHE_KEY = 'code_snippets.active';

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /** "about", "/about/", "https://site.com/about?x=1", "home" or "/" become "/about" or "/". A trailing * is a wildcard ("/blog/*"). */
    public static function normalizePath(string $entry): ?string
    {
        $entry = trim($entry);
        if ($entry === '') {
            return null;
        }
        if (preg_match('#^https?://#i', $entry)) {
            $entry = (string) parse_url($entry, PHP_URL_PATH);
        }
        $entry = preg_replace('/[?#].*$/', '', $entry);
        $entry = '/'.trim($entry, '/');
        if (in_array($entry, ['/home', '/index'], true)) {
            return '/';
        }

        return $entry === '' ? '/' : $entry;
    }

    /** @return list<string> */
    public static function parsePaths(?string $text): array
    {
        return array_values(array_unique(array_filter(array_map([self::class, 'normalizePath'], preg_split('/[\r\n,]+/', (string) $text)))));
    }

    private static function matches(array $patterns, string $path): bool
    {
        foreach ($patterns as $pattern) {
            if ($pattern === $path || Str::is($pattern, $path)) {
                return true;
            }
        }

        return false;
    }

    public function appliesTo(string $path): bool
    {
        if (self::matches(self::parsePaths($this->exclude_paths), $path)) {
            return false;
        }

        return $this->scope !== 'only' || self::matches(self::parsePaths($this->include_paths), $path);
    }

    /** The raw code of every enabled snippet for this place on this page, in the saved order. */
    public static function render(string $position, ?string $path = null): string
    {
        // rescue(): a site that has not run the migration yet must keep working (no snippets, no error)
        // a per-request copy (render() runs several times per page; each cache read is a database query on the database cache store)
        static $memo = null;
        $load = fn () => rescue(fn () => Cache::rememberForever(self::CACHE_KEY, fn () => self::where('is_active', true)->orderBy('sort_order')->orderBy('id')->get()->all()), [], false);
        if (app()->runningInConsole()) {
            $all = $load();
        } else {
            $memo ??= new \WeakMap();
            $all = $memo[app('request')] ??= $load();
        }
        $path ??= self::normalizePath('/'.request()->path());

        return collect($all)
            ->filter(fn (self $s) => $s->position === $position && $s->appliesTo($path))
            ->map(fn (self $s) => $s->code)
            ->implode("\n");
    }
}
