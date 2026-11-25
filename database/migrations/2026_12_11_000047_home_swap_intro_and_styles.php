<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Home: "Why travel with us" (split_list) now comes before "Choose your style" (tour_types). */
return new class extends Migration
{
    public function up(): void
    {
        $page = DB::table('pages')->where('slug', 'home')->first(['id', 'sections']);
        $s = $page ? json_decode((string) $page->sections, true) : null;
        if (! is_array($s)) {
            return;
        }
        $types = array_column($s, 'type');
        $a = array_search('tour_types', $types);
        $b = array_search('split_list', $types);
        if ($a === false || $b === false) {
            return;
        }
        [$s[$a], $s[$b]] = [$s[$b], $s[$a]];
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode(array_values($s), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
