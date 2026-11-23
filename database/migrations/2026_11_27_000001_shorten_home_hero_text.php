<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** The home banner text is now 2 to 3 lines. Only the old default text is replaced; edited text is kept. */
    public function up(): void
    {
        $new = 'Windmills, tulip fields and hidden gems, on private self drive and chauffeur tours or small-group coach tours. Answer a few quick questions for a free quote.';
        foreach (DB::table('pages')->whereNotNull('sections')->get(['id', 'sections']) as $page) {
            $sections = json_decode((string) $page->sections, true);
            if (! is_array($sections)) {
                continue;
            }
            $changed = false;
            foreach ($sections as &$s) {
                if (($s['type'] ?? '') === 'hero_video_quiz' && str_starts_with((string) ($s['data']['subheading'] ?? ''), 'Windmills, tulip fields, cheese markets and hidden gems, on')) {
                    $s['data']['subheading'] = $new;
                    $changed = true;
                }
            }
            unset($s);
            if ($changed) {
                DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
            }
        }
    }
};
