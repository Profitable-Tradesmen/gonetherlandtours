<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'noindex' => 'boolean'];
    }

    public function packages()
    {
        return $this->hasMany(Package::class);
    }

    /** Ids of the packages in this destination (Admin > Destinations > Tours). */
    public function tourIds(): array
    {
        return $this->exists ? $this->packages()->pluck('id')->all() : [];
    }

    /** Package titles for the Tours column in the admin list. */
    public function getTourListAttribute(): array
    {
        return $this->packages()->orderBy('sort_order')->get()->map(fn ($p) => $p->title.($p->is_published ? '' : ' (unpublished)'))->all();
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true)->orderBy('sort_order');
    }
}
