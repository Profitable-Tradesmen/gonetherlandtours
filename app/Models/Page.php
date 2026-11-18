<?php

namespace App\Models;

use App\Support\Sections;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * A page of the site: SEO fields plus an ordered list of sections (see App\Support\Sections) edited in Admin > Pages.
 * sections = [['id' => 'ab12cd34', 'type' => 'faq', 'visible' => true, 'data' => [...]], ...]
 */
class Page extends Model
{
    protected $guarded = [];

    /** URL paths that custom pages may not use (they belong to the app). */
    public const RESERVED = ['admin', 'packages', 'destinations', 'tours', 'blog', 'enquiry', 'thank-you', 'sitemap.xml', 'robots.txt', 'storage', 'build', 'images', 'videos', 'login', 'up', 'about', 'contact', 'faq', 'testimonials', 'home', 'terms-and-conditions', 'privacy-policy'];

    /** System pages that live at a fixed URL. */
    private const FIXED_URLS = ['home' => '/', 'about' => '/about', 'contact' => '/contact', 'faq' => '/faq', 'testimonials' => '/testimonials', 'terms' => '/terms-and-conditions', 'privacy' => '/privacy-policy'];

    protected function casts(): array
    {
        return ['content' => 'array', 'sections' => 'array', 'noindex' => 'boolean', 'is_published' => 'boolean', 'is_system' => 'boolean'];
    }

    public static function bySlug(string $slug): self
    {
        return static::where('slug', $slug)->firstOrFail();
    }

    /** Path of this page on the site, e.g. "/about" or "/my-custom-page". */
    public function path(): string
    {
        if ($this->is_system && str_starts_with($this->slug, 'tour-')) {
            return '/tours/'.substr($this->slug, 5);   // companion page of a tour type: its public address is the tour type's
        }

        return self::FIXED_URLS[$this->slug] ?? '/'.$this->slug;
    }

    public function url(): string
    {
        return url($this->path());
    }

    /** @return array<int,array{id:string,type:string,visible:bool,data:array}> */
    public function allSections(): array
    {
        return array_values(array_filter((array) $this->sections, fn ($s) => is_array($s) && isset($s['id'], $s['type']) && Sections::definition($s['type'])));
    }

    /** Visible sections with their data completed from the defaults, ready for the views. */
    public function visibleSections(): array
    {
        return collect($this->allSections())
            ->filter(fn ($s) => $s['visible'] ?? true)
            ->map(fn ($s) => ['id' => $s['id'], 'type' => $s['type'], 'd' => Sections::resolve($s['type'], (array) ($s['data'] ?? []))])
            ->values()->all();
    }

    public function hasSection(string $type): bool
    {
        return collect($this->visibleSections())->contains('type', $type);
    }

    public static function newSectionId(): string
    {
        return Str::lower(Str::random(8));
    }
}
