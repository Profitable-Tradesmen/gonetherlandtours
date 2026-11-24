<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Home numbers band: the background photo is now the Kinderdijk windmills at dusk (matches the "19 windmills" number). */
return new class extends Migration
{
    public function up(): void
    {
        $page = DB::table('pages')->where('slug', 'home')->first(['id', 'sections']);
        $sections = $page ? json_decode((string) $page->sections, true) : null;
        if (! is_array($sections)) {
            return;
        }
        foreach ($sections as &$s) {
            if (($s['type'] ?? '') === 'stats_band') {
                $s['data']['image'] = 'images/stats-kinderdijk.webp';
                $s['data']['image_alt'] = 'The Kinderdijk windmills along a calm canal at dusk';
            }
        }
        unset($s);
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
