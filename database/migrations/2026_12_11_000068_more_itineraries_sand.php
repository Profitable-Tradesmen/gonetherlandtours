<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** "More itineraries" goes sand; the destinations section above it goes white so the two do not run together. */
return new class extends Migration
{
    public function up(): void
    {
        $page = DB::table('pages')->where('slug', 'home')->first(['id', 'sections']);
        $s = $page ? json_decode((string) $page->sections, true) : null;
        if (! is_array($s)) {
            return;
        }
        foreach ($s as &$x) {
            if (($x['type'] ?? '') === 'destination_cards') {
                $x['data']['background'] = 'white';
            }
            if (($x['type'] ?? '') === 'tour_packages' && ($x['data']['eyebrow'] ?? '') === 'More itineraries') {
                $x['data']['background'] = 'sand';
            }
        }
        unset($x);
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($s, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
