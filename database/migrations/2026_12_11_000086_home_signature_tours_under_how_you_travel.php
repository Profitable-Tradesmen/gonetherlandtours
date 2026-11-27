<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Home page: "Our signature tours" moves right under "How you travel" and turns sand. The sections after it are ordered so light and
 * navy bands alternate: your own pace, who you travel with, shaped around you, gallery.
 */
return new class extends Migration
{
    public function up(): void
    {
        $page = DB::table('pages')->where('slug', 'home')->first(['id', 'sections']);
        $sections = $page ? json_decode((string) $page->sections, true) : null;
        if (! is_array($sections) || ! in_array('tour_types', array_column($sections, 'type'), true)) {
            return;
        }
        $take = function (string $type) use (&$sections) {
            $i = array_search($type, array_column($sections, 'type'), true);

            return $i === false ? null : array_splice($sections, $i, 1)[0];
        };
        $tours = $take('packages_tabs');
        if ($tours) {
            $tours['data']['background'] = 'sand';
        }
        $move = array_values(array_filter([$tours, $take('photo_band'), $take('image_strip'), $take('stats_band')]));
        array_splice($sections, array_search('tour_types', array_column($sections, 'type'), true) + 1, 0, $move);
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
