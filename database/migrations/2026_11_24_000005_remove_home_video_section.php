<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The "See the Netherlands before you go" video band is no longer part of the home page. On the home page an untouched one
     * (still the default heading) is removed; one an admin edited is only hidden, so the work is not lost. Other pages are not touched.
     * The "Video feature" section type stays available in Admin > Pages > Add section.
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
            if (($s['type'] ?? '') !== 'video_feature') {
                continue;
            }
            if (($s['data']['heading'] ?? 'See the Netherlands *before you go*') === 'See the Netherlands *before you go*') {
                unset($sections[$i]);
            } else {
                $sections[$i]['visible'] = false;
            }
            $changed = true;
        }
        if ($changed) {
            DB::table('pages')->where('id', $home->id)->update(['sections' => json_encode(array_values($sections), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
        }
    }

    public function down(): void
    {
        // Layout change only: nothing to undo.
    }
};
