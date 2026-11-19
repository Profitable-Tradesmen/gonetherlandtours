<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** The closing banner got a small label and an accent word in its heading. Only the old default heading is changed. */
    public function up(): void
    {
        foreach (DB::table('pages')->whereNotNull('sections')->get(['id', 'sections']) as $page) {
            $sections = json_decode((string) $page->sections, true);
            if (! is_array($sections)) {
                continue;
            }
            $changed = false;
            foreach ($sections as &$s) {
                if (($s['type'] ?? '') === 'cta_banner' && ($s['data']['heading'] ?? '') === 'Ready to Discover the Real Netherlands?') {
                    $s['data']['heading'] = 'Ready to Discover the *Real Netherlands*?';
                    $s['data']['eyebrow'] = $s['data']['eyebrow'] ?? 'Free quote';
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
