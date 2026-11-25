<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Home: eyebrows of the destinations and "your journey" sections follow the sister site ("Handpicked itineraries", "Featured journeys"). */
return new class extends Migration
{
    public function up(): void
    {
        $page = DB::table('pages')->where('slug', 'home')->first(['id', 'sections']);
        $s = $page ? json_decode((string) $page->sections, true) : null;
        if (! is_array($s)) {
            return;
        }
        $map = ['destination_cards' => 'Handpicked itineraries', 'image_strip' => 'Featured journeys'];
        foreach ($s as &$x) {
            if (isset($map[$x['type'] ?? ''])) {
                $x['data']['eyebrow'] = $map[$x['type']];
            }
        }
        unset($x);
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($s, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
