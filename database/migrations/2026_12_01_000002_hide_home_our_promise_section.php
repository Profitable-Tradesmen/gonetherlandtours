<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** The "Our promise / What that looks like in practice" section is hidden on the home page to shorten it (it can be switched back on in Admin > Pages > Home). */
    public function up(): void
    {
        $page = DB::table('pages')->where('slug', 'home')->first();
        $sections = $page ? json_decode((string) $page->sections, true) : null;
        if (! is_array($sections)) {
            return;
        }
        $changed = false;
        foreach ($sections as &$s) {
            if (($s['type'] ?? '') === 'usp_proof' && ($s['visible'] ?? true)) {
                $s['visible'] = false;
                $changed = true;
            }
        }
        unset($s);
        if ($changed) {
            DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
        }
    }
};
