<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Home photo band: three short icon points under the text. */
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
            if (($x['type'] ?? '') === 'photo_band' && ($x['data']['image'] ?? '') === 'images/feature_band.webp') {
                $x['data']['points'] = [
                    ['icon' => 'map', 'text' => 'Routes built around your pace'],
                    ['icon' => 'shield', 'text' => 'Clear pricing, no hidden fees'],
                    ['icon' => 'phone', 'text' => 'One specialist from start to finish'],
                ];
            }
        }
        unset($x);
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($s, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
