<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * On the home page the "Tour types" section comes right before the "Signature Collection" (packages) section.
     * If it currently sits somewhere after it, it is moved up; nothing else changes. Safe to run again.
     */
    public function up(): void
    {
        $home = DB::table('pages')->where('slug', 'home')->first();
        $sections = $home ? json_decode((string) $home->sections, true) : null;
        if (! is_array($sections)) {
            return;
        }
        $types = array_column($sections, 'type');
        $tt = array_search('tour_types', $types, true);
        $pk = array_search('packages_tabs', $types, true);
        if ($tt === false || $pk === false || $tt < $pk) {
            return;
        }

        $moved = array_splice($sections, $tt, 1);
        array_splice($sections, $pk, 0, $moved);   // $pk is unaffected: the removed section was after it
        DB::table('pages')->where('id', $home->id)->update(['sections' => json_encode(array_values($sections), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }

    public function down(): void
    {
        // Order only: nothing to undo.
    }
};
