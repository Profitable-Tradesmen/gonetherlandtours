<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Home order and wording of the journeys section follow the sister site: ways to travel, intro, then "Handpicked journeys". */
return new class extends Migration
{
    public function up(): void
    {
        $page = DB::table('pages')->where('slug', 'home')->first(['id', 'sections']);
        $s = $page ? json_decode((string) $page->sections, true) : null;
        if (! is_array($s)) {
            return;
        }
        foreach ($s as &$x) {
            if (($x['type'] ?? '') === 'packages_tabs') {
                $x['data']['eyebrow'] = 'Handpicked journeys';
                $x['data']['heading'] = 'Our top-rated *Netherlands tours*';
                $x['data']['intro'] = 'Hand-built journeys through the canals, tulip fields and coast, in every season.';
                $x['data']['all_label'] = 'All packages';
                $x['data']['button_label'] = 'View all packages';
            }
        }
        unset($x);
        $types = array_column($s, 'type');
        $t = array_search('tour_types', $types);
        $i = array_search('split_list', $types);
        if ($t !== false && $i !== false && $t > $i) {
            $tt = array_splice($s, $t, 1);
            array_splice($s, $i, 0, $tt);
        }
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode(array_values($s), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
