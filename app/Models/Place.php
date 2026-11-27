<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A town or sight, managed in Admin > Places. Tour routes are matched to places by name. */
class Place extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'lat' => 'float', 'lon' => 'float'];
    }

    public function destination()
    {
        return $this->belongsTo(Destination::class);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true)->orderBy('sort_order');
    }

    /** The places named in a list of route stops, in route order. Stops without a place are skipped. */
    public static function forStops(array $stops)
    {
        $all = static::active()->get()->keyBy(fn ($p) => mb_strtolower($p->name));

        return collect($stops)->map(fn ($s) => $all->get(mb_strtolower($s)))->filter()->unique('id')->values();
    }

    public function getRegionNameAttribute(): string
    {
        return (string) $this->destination?->name;
    }
}
