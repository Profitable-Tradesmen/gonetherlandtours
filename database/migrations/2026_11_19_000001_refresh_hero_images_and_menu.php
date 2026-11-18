<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Design refresh for existing sites (fresh installs get the new defaults from DefaultSections):
     *  - hero sections still using the old abstract placeholder illustrations get the new hero photos
     *  - the home testimonials become the one-at-a-time "spotlight" style (only if still on the old default "dark")
     *  - the header menu gets a "Destinations" link (to the new /destinations page)
     * Anything an admin already changed (a custom photo, another testimonial style, their own menu) is left alone.
     */
    public function up(): void
    {
        $oldHero = ['', 'images/hero-video-poster.webp'];
        foreach (DB::table('pages')->whereNotNull('sections')->get(['id', 'slug', 'sections']) as $page) {
            $sections = json_decode((string) $page->sections, true);
            if (! is_array($sections)) {
                continue;
            }
            $changed = false;
            foreach ($sections as &$s) {
                $d = &$s['data'];
                if (($s['type'] ?? '') === 'hero_video_quiz' && in_array($d['poster'] ?? '', $oldHero, true)) {
                    $d['poster'] = 'images/hero-home.webp';
                    $changed = true;
                }
                if (($s['type'] ?? '') === 'video_feature' && in_array($d['poster'] ?? '', $oldHero, true)) {
                    $d['poster'] = 'images/hero-about.webp';
                    $changed = true;
                }
                if (($s['type'] ?? '') === 'hero_seasons' && in_array($d['image'] ?? '', ['', 'images/hero.webp'], true)) {
                    $d['image'] = 'images/hero-about.webp';
                    $changed = true;
                }
                if ($page->slug === 'home' && ($s['type'] ?? '') === 'testimonials' && ($d['variant'] ?? 'dark') === 'dark') {
                    $d['variant'] = 'spotlight';
                    $changed = true;
                }
                unset($d);
            }
            unset($s);
            if ($changed) {
                DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
            }
        }

        if (! DB::table('menu_items')->where('menu', 'header')->whereNull('parent_id')->where('url', '/destinations')->exists()) {
            $tours = DB::table('menu_items')->where('menu', 'header')->whereNull('parent_id')->where('kind', 'tours')->value('sort_order');
            $after = $tours ?? 0;
            DB::table('menu_items')->where('menu', 'header')->whereNull('parent_id')->where('sort_order', '>', $after)->increment('sort_order');
            DB::table('menu_items')->insert([
                'menu' => 'header', 'parent_id' => null, 'kind' => 'link', 'label' => 'Destinations', 'description' => null, 'url' => '/destinations',
                'image' => null, 'image_alt' => null, 'new_tab' => false, 'sort_order' => $after + 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('menu_items')->where('menu', 'header')->where('url', '/destinations')->delete();
    }
};
