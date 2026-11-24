<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Home: a decorative Dutch skyline divider between the trust strip and the intro section (idea from the sister Go Austria site). */
return new class extends Migration
{
    public function up(): void
    {
        $page = DB::table('pages')->where('slug', 'home')->first(['id', 'sections']);
        $sections = $page ? json_decode((string) $page->sections, true) : null;
        if (! is_array($sections) || in_array('skyline_divider', array_column($sections, 'type'), true)) {
            return;
        }
        $out = [];
        foreach ($sections as $s) {
            $out[] = $s;
            if (($s['type'] ?? '') === 'value_strip') {
                $out[] = ['id' => 'k'.substr(md5('home-skyline'), 0, 10), 'type' => 'skyline_divider', 'visible' => true, 'data' => []];
            }
        }
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
