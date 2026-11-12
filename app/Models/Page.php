<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['content' => 'array', 'noindex' => 'boolean'];
    }

    public static function bySlug(string $slug): self
    {
        return static::where('slug', $slug)->firstOrFail();
    }

    /** Fetch a structured content value (home page copy) with a fallback. */
    public function c(string $key, string $default = ''): string
    {
        return (string) data_get($this->content, $key, $default);
    }
}
