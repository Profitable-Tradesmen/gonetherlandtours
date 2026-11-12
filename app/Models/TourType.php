<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourType extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function packages()
    {
        return $this->hasMany(Package::class);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true)->orderBy('sort_order');
    }
}
