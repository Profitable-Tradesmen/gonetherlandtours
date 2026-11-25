<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Home: the tulip photo band sat right on top of the mid-page call to action (two dark photo blocks in a row), so it is removed. */
return new class extends Migration
{
    public function up(): void
    {
        $page = DB::table('pages')->where('slug', 'home')->first(['id', 'sections']);
        $s = $page ? json_decode((string) $page->sections, true) : null;
        if (! is_array($s)) {
            return;
        }
        $s = array_values(array_filter($s, fn ($x) => ! (($x['type'] ?? '') === 'photo_band' && ($x['data']['image'] ?? '') === 'images/nh-cta.webp')));
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($s, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
