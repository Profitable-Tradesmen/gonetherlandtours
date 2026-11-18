<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourType extends Model
{
    protected $guarded = [];

    /** Tag and bullet points for the home page cards (slug => [tag, [points]]); used by the seeder and the migration. */
    public const CARD_DEFAULTS = [
        'self-drive' => ['Custom-built itinerary', ['Your own car and your own pace', 'Hotels booked for you', 'Routes planned around your dates']],
        'chauffeur-driven' => ['Private driver-guide', ['Local driver-guide for your own party', 'Hotels and routes arranged for you', 'Relaxed, door-to-door comfort']],
        'coach-tours' => ['Guided small-group tour', ['Led by a local guide', 'Sightseeing and transport included', 'Accommodation taken care of']],
    ];

    /** The card bullet points as a list. */
    public function cardPointList(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/',(string) $this->card_points))));
    }

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
