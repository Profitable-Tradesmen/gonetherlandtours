<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Home page: "Where to base your trip" turns sand and "More itineraries" turns white (their backgrounds swap). */
return new class extends Migration
{
    public function up(): void
    {
        $page = DB::table('pages')->where('slug', 'home')->first(['id', 'sections']);
        $sections = $page ? json_decode((string) $page->sections, true) : null;
        if (! is_array($sections)) {
            return;
        }
        foreach ($sections as $i => &$s) {
            if (($s['type'] ?? '') !== 'destination_cards') {
                continue;
            }
            $s['data']['background'] = 'sand';
            // "More itineraries" is the package grid right under the destinations.
            if (($sections[$i + 1]['type'] ?? '') === 'tour_packages') {
                $sections[$i + 1]['data']['background'] = 'white';
            }
        }
        unset($s);
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
