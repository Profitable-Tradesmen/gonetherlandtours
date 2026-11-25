<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** The mid-page call to action gets a canal-terrace photo (the aerial polder photo is already used in "Your journey. Your way."). */
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
            if (($x['type'] ?? '') === 'cta_banner' && ($x['data']['image'] ?? '') === 'images/cta-bg.webp') {
                $x['data']['image'] = 'images/hero-about.webp';
                $x['data']['image_alt'] = 'A Dutch canal lined with historic houses and busy café terraces';
            }
        }
        unset($x);
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($s, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
