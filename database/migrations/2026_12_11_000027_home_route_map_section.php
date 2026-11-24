<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** Home page: adds the "Route map" section (map of the Netherlands, clickable stops) right after the featured packages. */
return new class extends Migration
{
    public function up(): void
    {
        $page = DB::table('pages')->where('slug', 'home')->first(['id', 'sections']);
        $sections = $page ? json_decode((string) $page->sections, true) : null;
        if (! is_array($sections) || collect($sections)->contains('type', 'route_map')) {
            return;
        }
        $new = ['id' => Str::lower(Str::random(8)), 'type' => 'route_map', 'visible' => true, 'data' => [
            'eyebrow' => 'Plan your route',
            'heading' => 'See where your trip *could take you*',
            'text' => 'Canal cities, tulip fields and the quiet Zeeland coast are all within a few hours of each other. Pick a region to see the trips we plan there.',
            'tag' => 'Tulip season: April',
            'button_label' => 'Plan my route',
            'background' => 'white',
        ]];
        $at = collect($sections)->search(fn ($s) => ($s['type'] ?? '') === 'packages_tabs');
        array_splice($sections, $at === false ? 2 : $at + 1, 0, [$new]);
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
