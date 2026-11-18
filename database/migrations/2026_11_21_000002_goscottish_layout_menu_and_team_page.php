<?php

use App\Models\Page;
use App\Support\DefaultSections;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** The home page exactly as the previous release seeded it (section types, in order). */
    private const OLD_HOME = [
        'hero_video_quiz', 'ticker', 'split_list', 'packages_tabs', 'feature_band', 'tour_types', 'video_feature',
        'image_strip', 'steps', 'testimonials', 'gallery', 'text_cta', 'faq', 'blog_latest', 'cta_banner',
    ];

    /**
     * 1. Home page: hero, feature strip, intro, travel-style cards, offer band, packages by tour type ... (only when the home page
     *    still has the previously seeded order; every kept section keeps its id and the text the admin typed).
     * 2. Adds the "Meet the Team" page (a normal custom page) when the slug is free.
     * 3. Switches the header to Home, one dropdown per tour type and an About dropdown, ONLY while it is still the seeded menu.
     * 4. Adds "Meet the team" to the seeded footer links.
     */
    public function up(): void
    {
        $home = DB::table('pages')->where('slug', 'home')->first();
        $sections = $home ? json_decode((string) $home->sections, true) : null;
        if (is_array($sections) && array_column($sections, 'type') === self::OLD_HOME) {
            $by = array_column($sections, null, 'type');
            $ticker = $by['ticker'];
            $ticker['visible'] = false;       // kept, hidden: an admin can switch it back on
            $new = [
                $by['hero_video_quiz'], DefaultSections::featureStrip(), $by['split_list'], $by['tour_types'], DefaultSections::promoBand(), $by['packages_tabs'],
                $by['feature_band'], $by['video_feature'], $by['image_strip'], $by['steps'], $by['testimonials'], $by['gallery'], $by['text_cta'], $by['faq'], $by['blog_latest'], $by['cta_banner'],
                $ticker,
            ];
            DB::table('pages')->where('id', $home->id)->update(['sections' => json_encode($new, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
        }

        if (! DB::table('pages')->where('slug', 'meet-the-team')->exists()) {
            Page::create([
                'slug' => 'meet-the-team', 'title' => 'Meet the Team', 'h1' => 'Meet the Team',
                'seo_title' => 'Meet the Team | GoNetherlandTours',
                'meta_description' => 'Meet the travel specialists who plan your Netherlands trip and look after you along the way.',
                'sections' => DefaultSections::teamPage(), 'is_published' => true, 'is_system' => false,
            ]);
        }

        $top = DB::table('menu_items')->where('menu', 'header')->whereNull('parent_id')->orderBy('sort_order')->get(['label', 'kind']);
        $labels = $top->pluck('label')->all();
        $kinds = $top->pluck('kind')->all();
        $seeded = ($labels === ['Tours', 'Packages', 'About Us', 'Contact'] && $kinds === ['tours', 'link', 'cards', 'link'])
            || ($labels === ['Tours', 'Destinations', 'Packages', 'About Us', 'Contact'] && $kinds === ['tours', 'link', 'link', 'cards', 'link']);
        if ($seeded) {
            DefaultSections::resetHeaderMenu();
        }

        $footer = DB::table('menu_items')->where('menu', 'footer')->orderBy('sort_order')->get(['label', 'url']);
        if ($footer->count() === 7 && $footer->firstWhere('url', '/about') && ! $footer->firstWhere('url', '/meet-the-team')) {
            $now = now();
            DB::table('menu_items')->where('menu', 'footer')->where('sort_order', '>', 1)->increment('sort_order');
            DB::table('menu_items')->insert(['menu' => 'footer', 'label' => 'Meet the team', 'url' => '/meet-the-team', 'sort_order' => 2, 'kind' => 'link', 'description' => null, 'image' => null, 'image_alt' => null, 'parent_id' => null, 'is_active' => true, 'new_tab' => false, 'created_at' => $now, 'updated_at' => $now]);
        }
    }

    public function down(): void
    {
        // Layout change only: nothing to undo.
    }
};
