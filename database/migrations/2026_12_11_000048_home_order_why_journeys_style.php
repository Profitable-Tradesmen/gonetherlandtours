<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Home: Why travel with us, then Handpicked journeys, then Choose your style. */
return new class extends Migration
{
    public function up(): void
    {
        $page = DB::table('pages')->where('slug', 'home')->first(['id', 'sections']);
        $s = $page ? json_decode((string) $page->sections, true) : null;
        if (! is_array($s)) {
            return;
        }
        $pick = function (string $type) use (&$s) {
            foreach ($s as $i => $x) {
                if (($x['type'] ?? '') === $type) {
                    return array_splice($s, $i, 1)[0];
                }
            }
            return null;
        };
        $why = $pick('split_list');
        $pk = $pick('packages_tabs');
        $ty = $pick('tour_types');
        if (! $why || ! $pk || ! $ty) {
            return;
        }
        $at = 0;
        foreach ($s as $i => $x) {
            if (($x['type'] ?? '') === 'skyline_divider') {
                $at = $i + 1;
            }
        }
        $pk['data']['background'] = 'sand';
        array_splice($s, $at, 0, [$why, $pk, $ty]);
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode(array_values($s), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
