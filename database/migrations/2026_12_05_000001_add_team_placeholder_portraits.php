<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Gives the three team cards a drawn placeholder portrait (public/images/team-1..3.webp) instead of a numbered circle.
 * Only cards for the three starter names that still have no photo are touched, so real photos added in the admin stay.
 */
return new class extends Migration
{
    public function up(): void
    {
        $portraits = ['Anouk de Vries' => 1, 'Jeroen Bakker' => 2, 'Sanne Visser' => 3];
        foreach (DB::table('pages')->whereNotNull('sections')->get(['id', 'sections']) as $page) {
            $sections = json_decode((string) $page->sections, true);
            if (! is_array($sections)) {
                continue;
            }
            $changed = false;
            foreach ($sections as &$s) {
                if (($s['type'] ?? '') !== 'team' || ! is_array($s['data']['items'] ?? null)) {
                    continue;
                }
                foreach ($s['data']['items'] as &$m) {
                    $n = $portraits[$m['name'] ?? ''] ?? null;
                    if ($n && trim((string) ($m['image'] ?? '')) === '') {
                        $m['image'] = 'images/team-'.$n.'.webp';
                        $m['image_alt'] = 'Placeholder portrait of '.$m['name'];
                        $changed = true;
                    }
                }
                unset($m);
            }
            unset($s);
            if ($changed) {
                DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($sections, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]);
            }
        }
    }

    public function down(): void {}
};
