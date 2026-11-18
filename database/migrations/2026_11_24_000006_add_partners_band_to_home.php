<?php

use App\Support\DefaultSections;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Adds the partner logos band to the home page, right after "How it works" (the steps section). Skipped when there is already one. */
    public function up(): void
    {
        $home = DB::table('pages')->where('slug', 'home')->first();
        $sections = $home ? json_decode((string) $home->sections, true) : null;
        if (! is_array($sections)) {
            return;
        }
        $types = array_column($sections, 'type');
        if (in_array('partners', $types, true)) {
            return;
        }
        $at = array_search('steps', $types, true);
        if ($at === false) {
            return;   // an admin removed "How it works": do not guess where it belongs
        }
        array_splice($sections, $at + 1, 0, [DefaultSections::partners()]);
        DB::table('pages')->where('id', $home->id)->update(['sections' => json_encode(array_values($sections), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }

    public function down(): void
    {
        // Layout change only: nothing to undo.
    }
};
