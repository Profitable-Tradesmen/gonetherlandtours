<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Home hero: the new Amsterdam canal photo is the main photo, four more site photos fade in after it (slider), under a flat 30% black layer. */
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
                $x['data']['poster'] = 'images/hero-home.webp';
                $x['data']['show_video'] = false;
                $x['data']['slides'] = array_map(fn ($i) => ['image' => $i], [
                    'images/feature_band.webp', 'images/tour-self-drive-aerial.webp', 'images/tour-coach-aerial.webp', 'images/dest-tulips.webp',
                ]);
            }
        }
        unset($x);
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($s, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
