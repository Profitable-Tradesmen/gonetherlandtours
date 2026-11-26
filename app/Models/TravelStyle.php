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

    /** The tours ticked in Admin > Travel styles, in page order. */
    public function packages()
    {
        return $this->belongsToMany(Package::class)->withPivot('sort_order')->orderBy('package_travel_style.sort_order');
    }

    /** The published ones, for the public page. */
    public function tours()
    {
        return $this->packages()->published()->with(['tourType', 'destination', 'images'])->get();
    }

    public function tourIds(): array
    {
        return $this->exists ? $this->packages()->pluck('packages.id')->all() : [];
    }

    /** Package titles for the Tours column in the admin list. */
    public function getTourListAttribute(): array
    {
        return $this->packages()->get()->map(fn ($p) => $p->title.($p->is_published ? '' : ' (unpublished)'))->all();
    }
}
