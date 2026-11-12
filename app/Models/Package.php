<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'highlights' => 'array',
            'itinerary' => 'array',
            'inclusions' => 'array',
            'exclusions' => 'array',
            'faqs' => 'array',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'is_sample' => 'boolean',
            'noindex' => 'boolean',
            'price_from' => 'decimal:2',
        ];
    }

    public function tourType()
    {
        return $this->belongsTo(TourType::class);
    }

    public function destination()
    {
        return $this->belongsTo(Destination::class);
    }

    public function images()
    {
        return $this->belongsToMany(Media::class, 'package_images')->withPivot('sort_order')->orderBy('package_images.sort_order');
    }

    public function scopePublished($q)
    {
        return $q->where('is_published', true);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function priceLabel(): string
    {
        if (! $this->price_from) {
            return '[CONFIRM: price]';
        }
        $symbol = ['EUR' => '€', 'USD' => '$', 'GBP' => '£'][$this->currency] ?? $this->currency.' ';

        return $symbol.number_format((float) $this->price_from);
    }
}
