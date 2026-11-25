<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Home, lower sections: short lines under the headings of "How you travel" and "Made for you". */
return new class extends Migration
{
    public function up(): void
    {
        $page = DB::table('pages')->where('slug', 'home')->first(['id', 'sections']);
        $s = $page ? json_decode((string) $page->sections, true) : null;
        if (! is_array($s)) {
            return;
        }
        $intro = [
            'tour_types' => 'Each way to travel comes with the routes, hotels and pace planned for you.',
            'image_strip' => 'Every trip is shaped around the people taking it. Quotes are free, with no obligation.',
        ];
        foreach ($s as &$x) {
            if (isset($intro[$x['type'] ?? ''])) {
                $x['data']['intro'] = $intro[$x['type']];
            }
        }
        unset($x);
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($s, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
