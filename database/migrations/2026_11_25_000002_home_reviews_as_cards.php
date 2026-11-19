<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The home page reviews are shown as three cards over a photo (style "dark") instead of one review at a time. Only the home page's
     * reviews section is changed, and only while it still uses the previously seeded style "spotlight". Pick "Spotlight" again in
     * Admin > Pages > Home to bring the one-at-a-time version back.
     */
    public function up(): void
    {
        $home = DB::table('pages')->where('slug', 'home')->first();
        $sections = $home ? json_decode((string) $home->sections, true) : null;
        if (! is_array($sections)) {
            return;
        }
        $changed = false;
        foreach ($sections as $i => $s) {
            if (($s['type'] ?? '') === 'testimonials' && ($s['data']['variant'] ?? '') === 'spotlight') {
                $sections[$i]['data']['variant'] = 'dark';
                $changed = true;
            }
        }
        if ($changed) {
            DB::table('pages')->where('id', $home->id)->update(['sections' => json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
        }
    }

    public function down(): void
    {
        // Style choice only: nothing to undo.
    }
};
