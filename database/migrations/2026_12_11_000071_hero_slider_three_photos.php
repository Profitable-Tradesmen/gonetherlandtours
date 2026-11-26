<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Home hero slider: the Amsterdam canal photo plus two sharp, large photos only (Zaanse Schans and Zeeland). */
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
            if (($x['type'] ?? '') === 'hero_video_quiz') {
                $x['data']['slides'] = [['image' => 'images/feature_band.webp'], ['image' => 'images/dest-zeeland.webp']];
            }
        }
        unset($x);
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($s, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
