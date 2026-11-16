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

    /** The original hard-coded gallery. Used until an admin saves their own (and to pre-fill the admin form). */
    public static function defaultGallery(): array
    {
        return [
            'eyebrow' => 'Visual journey',
            'title' => 'A glimpse of the *real Netherlands*',
            'items' => [
                ['caption' => 'First-time visitors', 'image' => 'images/first_time_visitors.webp', 'alt' => 'Amsterdam canal houses and a canal boat on a bright morning'],
                ['caption' => 'Couples', 'image' => 'images/couples.webp', 'alt' => 'A couple dining at a candle-lit canal-side restaurant at dusk'],
                ['caption' => 'Families', 'image' => 'images/families.webp', 'alt' => 'A family cycling past a windmill and green fields'],
                ['caption' => 'Culture lovers', 'image' => 'images/culture_lovers.webp', 'alt' => 'A market vendor stacking wheels of Gouda cheese'],
                ['caption' => 'Off the beaten path', 'image' => 'images/off_the_beaten_path.webp', 'alt' => 'A quiet, empty Dutch harbour village with colourful boats in morning mist'],
                ['caption' => 'Special occasions', 'image' => 'images/special_occasions.webp', 'alt' => 'A table set with champagne on a terrace overlooking a canal at sunset'],
            ],
        ];
    }

    /** Gallery settings as saved in Admin > Pages > Home (falls back to the defaults until first saved). */
    public function gallery(): array
    {
        $saved = data_get($this->content, 'gallery');

        return is_array($saved) ? $saved + self::defaultGallery() : self::defaultGallery();
    }

    /** Should the home page show the gallery? Shown by default; ticked off in the admin = hidden; also hidden when it has no photos. */
    public function showGallery(): bool
    {
        return (bool) data_get($this->content, 'show_gallery', true) && ! empty($this->gallery()['items']);
    }

    /** Fetch a structured content value (home page copy) with a fallback. */
    public function c(string $key, string $default = ''): string
    {
        return (string) data_get($this->content, $key, $default);
    }
}
