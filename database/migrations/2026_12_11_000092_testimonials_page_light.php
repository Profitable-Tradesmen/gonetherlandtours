<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Testimonials page: the reviews had a photo background directly under the photo banner. They now sit on plain white with a
 * heading, so the page reads photo, white, sand, photo.
 */
return new class extends Migration
{
    public function up(): void
    {
        $page = DB::table('pages')->where('slug', 'testimonials')->first(['id', 'sections']);
        $sections = $page ? json_decode((string) $page->sections, true) : null;
        if (! is_array($sections)) {
            return;
        }
        foreach ($sections as &$s) {
            if (($s['type'] ?? '') === 'testimonials') {
                $s['data']['variant'] = 'light';
                $s['data']['background'] = 'white';
                $s['data']['eyebrow'] = ($s['data']['eyebrow'] ?? '') ?: 'Reviews';
                $s['data']['heading'] = ($s['data']['heading'] ?? '') ?: 'Traveler *stories*';
            }
        }
        unset($s);
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
