<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The "why us" band now has a full background (photo, GIF or video) instead of a photo beside the tiles. A band that still has the
     * default side photo and no background yet gets that photo as its background and loses the side photo. A band an admin changed
     * (different photo, or a background already set) is left alone.
     */
    public function up(): void
    {
        foreach (DB::table('pages')->whereNotNull('sections')->get(['id', 'sections']) as $page) {
            $sections = json_decode((string) $page->sections, true);
            if (! is_array($sections)) {
                continue;
            }
            $changed = false;
            foreach ($sections as $i => $s) {
                if (($s['type'] ?? '') !== 'why_band' || array_key_exists('bg', $s['data'] ?? []) || ($s['data']['image'] ?? '') !== 'images/feature_band.webp') {
                    continue;
                }
                $sections[$i]['data']['bg'] = 'images/feature_band.webp';
                $sections[$i]['data']['image'] = '';
                $sections[$i]['data']['image_alt'] = '';
                $changed = true;
            }
            if ($changed) {
                DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
            }
        }
    }

    public function down(): void
    {
        // Layout change only: nothing to undo.
    }
};
