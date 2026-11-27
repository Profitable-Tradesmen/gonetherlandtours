<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Home page "Traveler stories" shows on the live site too, so it matches the local page. Sample reviews keep their "Sample review"
 * label. To leave samples off the live site again, tick "show real reviews only" in the section (Admin > Pages > Home).
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
            if (($s['type'] ?? '') === 'testimonials') {
                $s['data']['real_only_live'] = false;
            }
        }
        unset($s);
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
