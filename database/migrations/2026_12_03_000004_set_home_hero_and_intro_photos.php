<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The home hero and the "real Netherlands" split section keep their photo paths in the page's JSON, so a pull alone
 * does not change them on a server whose copy still points at older uploads. Sets them to the new files in public/images.
 */
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
            $type = $s['type'] ?? '';
            if ($type === 'hero_video_quiz') {
                $s['data']['poster'] = 'images/hero-home.webp';
            }
            if ($type === 'split_list') {
                $s['data']['image1'] = 'images/Intro_image_split.webp';
                $s['data']['image2'] = 'images/Intro_image_split1.webp';
            }
        }
        unset($s);
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($sections, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]);
    }

    public function down(): void {}
};
