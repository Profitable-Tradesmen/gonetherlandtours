<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** The packages section heading is now "Signature Collection". Only the untouched old default heading is renamed. */
    public function up(): void
    {
        foreach (DB::table('pages')->whereNotNull('sections')->get(['id', 'sections']) as $page) {
            $sections = json_decode((string) $page->sections, true);
            if (! is_array($sections)) {
                continue;
            }
            $changed = false;
            foreach ($sections as &$s) {
                if (($s['type'] ?? '') === 'packages_tabs' && ($s['data']['heading'] ?? '') === 'Signature Netherlands Journeys') {
                    $s['data']['heading'] = 'Signature Collection';
                    $changed = true;
                }
            }
            unset($s);
            if ($changed) {
                DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
            }
        }
    }

    public function down(): void
    {
        // Wording only: nothing to undo.
    }
};
