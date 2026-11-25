<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** The "Start here" band uses the same navy photo overlay as the numbers band. */
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
            if (($x['type'] ?? '') === 'text_cta' && ($x['data']['eyebrow'] ?? '') === 'Start here') {
                $x['data']['overlay'] = 'navy';
                $x['data']['image'] = 'images/hero-about.webp';
            }
        }
        unset($x);
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($s, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
