<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Home page: "How you travel" (tour types) moves right under "Why choose us". The "Travel at your own pace" band follows it, ahead of
 * the signature tours, so two white sections or two navy bands never end up next to each other.
 */
return new class extends Migration
{
    public function up(): void
    {
        $page = DB::table('pages')->where('slug', 'home')->first(['id', 'sections']);
        $sections = $page ? json_decode((string) $page->sections, true) : null;
        if (! is_array($sections)) {
            return;
        }
        $take = function (string $type) use (&$sections) {
            $i = array_search($type, array_column($sections, 'type'), true);

            return $i === false ? null : array_splice($sections, $i, 1)[0];
        };
        if (! in_array('split_list', array_column($sections, 'type'), true)) {
            return;
        }
        $move = array_values(array_filter([$take('tour_types'), $take('photo_band')]));
        array_splice($sections, array_search('split_list', array_column($sections, 'type'), true) + 1, 0, $move);
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
