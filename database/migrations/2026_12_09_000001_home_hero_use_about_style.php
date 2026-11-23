<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** The home hero adopts the About page look: the original windmill photo is kept, with the key words of the heading in gold italics. */
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
            if (($s['type'] ?? '') !== 'hero_video_quiz') {
                continue;
            }
            $s['data']['poster'] = 'images/hero-home.webp';
            if (($s['data']['heading'] ?? '') === 'Discover the Authentic Netherlands') {
                $s['data']['heading'] = 'Discover the *Authentic Netherlands*';
            }
        }
        unset($s);
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($sections, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]);
    }
};
