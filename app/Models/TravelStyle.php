<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A way of traveling by interest (tulips, canals, cheese...), managed in Admin > Travel styles. */
class TravelStyle extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'noindex' => 'boolean'];
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true)->orderBy('sort_order');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** Lines of a text field (one entry per line), or comma-separated entries. */
    public function list(string $field, string $separator = "\n"): array
    {
        return array_values(array_filter(array_map('trim', explode($separator, str_replace("\r", '', (string) $this->{$field})))));
    }

    /** The published tours of this style, in the order typed in the admin. */
    public function tours()
    {
        $slugs = $this->list('tour_slugs');

        return Package::published()->whereIn('slug', $slugs)->with(['tourType', 'destination', 'images'])->get()
            ->sortBy(fn ($p) => array_search($p->slug, $slugs))->values();
    }
}
