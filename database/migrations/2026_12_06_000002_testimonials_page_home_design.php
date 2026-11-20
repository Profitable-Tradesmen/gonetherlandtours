<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The Testimonials page gets the same design as the reviews on the home page (cards over a photo). Only a section still on the old
 * default (the light style with no heading) is changed, so a style or heading chosen in the admin is kept.
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
        $changed = false;
        foreach ($sections as &$s) {
            if (($s['type'] ?? '') === 'testimonials' && ($s['data']['variant'] ?? '') === 'light' && trim((string) ($s['data']['heading'] ?? '')) === '') {
                $s['data']['variant'] = 'dark';
                // no heading of its own: the page title bar above already says "What our travelers say"
                $s['data']['eyebrow'] = '';
                $changed = true;
            }
        }
        unset($s);
        if ($changed) {
            DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($sections, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]);
        }
    }

    public function down(): void {}
};
