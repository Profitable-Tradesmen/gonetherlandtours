<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Home page: "Find your corner of the Netherlands", "Routes travelers book again and again" and "Trips that fit your budget" are
 * switched off (they can be turned on again in Admin > Pages). The "Have a trip in mind?" band moves below the photo gallery, so two
 * navy bands or two white sections do not end up next to each other.
 */
return new class extends Migration
{
    public function up(): void
    {
        $page = DB::table('pages')->where('slug', 'home')->first(['id', 'sections']);
        $sections = $page ? json_decode((string) $page->sections, true) : null;
        if (! is_array($sections)) {
            return;
        }
        // Destinations and the two package grids (the featured tours above them are a different section type).
        foreach ($sections as &$s) {
            if (in_array($s['type'] ?? '', ['destination_cards', 'tour_packages'], true)) {
                $s['visible'] = false;
            }
        }
        unset($s);
        $cta = array_search('text_cta', array_column($sections, 'type'), true);
        if ($cta !== false && in_array('gallery', array_column($sections, 'type'), true)) {
            $block = array_splice($sections, $cta, 1)[0];
            array_splice($sections, array_search('gallery', array_column($sections, 'type'), true) + 1, 0, [$block]);
        }
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
