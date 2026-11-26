<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Home page: guest reviews shown after the photo gallery (sample reviews stay off the live site) and the numbers band now explains
 * how the company plans a trip. "Contact" moves from the About us dropdown to the main menu.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->menu();

        $page = DB::table('pages')->where('slug', 'home')->first(['id', 'sections']);
        $sections = $page ? json_decode((string) $page->sections, true) : null;
        if (! is_array($sections)) {
            return;
        }
        $reviews = null;
        foreach ($sections as $i => &$s) {
            if (($s['type'] ?? '') === 'stats_band') {
                $s['data'] = array_merge($s['data'] ?? [], [
                    'eyebrow' => 'The way we plan',
                    'heading' => 'Shaped *around you*',
                    'text' => 'You tell us how you like to travel. We turn it into a route, with the details settled before you leave home.',
                    'points' => [
                        ['text' => 'Routes shaped around your dates, pace and interests'],
                        ['text' => 'Clear prices in euros, confirmed in writing'],
                        ['text' => 'A travel specialist to help before you go and while you travel'],
                    ],
                    'items' => [
                        ['value' => '12', 'label' => 'Itineraries ready to adapt'],
                        ['value' => '3', 'label' => 'Ways to travel'],
                        ['value' => '3', 'label' => 'Regions to explore'],
                        ['value' => '€0', 'label' => 'Cost of your quote'],
                    ],
                    'button_label' => 'Get a free quote',
                    'link_label' => 'Meet the team',
                    'link_url' => '/meet-the-team',
                ]);
            }
            if (($s['type'] ?? '') === 'testimonials') {
                $reviews = $i;
            }
        }
        unset($s);
        if ($reviews !== null) {
            $block = array_splice($sections, $reviews, 1)[0];
            $block['visible'] = true;
            $block['data'] = array_merge($block['data'] ?? [], ['heading' => 'Traveler stories, *in their words*', 'real_only_live' => true]);
            $after = array_search('gallery', array_column($sections, 'type'), true);
            $at = $after === false ? $reviews : $after + 1;
            array_splice($sections, $at, 0, [$block]);
            // The reviews are on sand, so a sand package grid right after them turns white to keep the two apart.
            if (($sections[$at + 1]['type'] ?? '') === 'tour_packages') {
                $sections[$at + 1]['data']['background'] = 'white';
            }
        }
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }

    private function menu(): void
    {
        $header = fn () => DB::table('menu_items')->where('menu', 'header');
        if (! $header()->exists() || $header()->whereNull('parent_id')->where('url', '/contact')->exists()) {
            return;
        }
        $header()->whereNotNull('parent_id')->where('url', '/contact')->delete();
        DB::table('menu_items')->insert([
            'menu' => 'header', 'kind' => 'link', 'label' => 'Contact', 'url' => '/contact', 'new_tab' => 0, 'is_active' => 1,
            'sort_order' => (int) $header()->whereNull('parent_id')->max('sort_order') + 1, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }
};
