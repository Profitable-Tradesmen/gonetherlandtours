<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Home gallery: "Amsterdam after dark" uses a real blue-hour canal photo (Jorge Franganillo, CC BY 2.0, credited in the footer); this updates its alt text. */
return new class extends Migration
{
    public function up(): void
    {
        $page = DB::table('pages')->where('slug', 'home')->first(['id', 'sections']);
        $sections = $page ? json_decode((string) $page->sections, true) : null;
        if (! is_array($sections)) {
            return;
        }
        foreach ($sections as &$s) {
            foreach ($s['data']['items'] ?? [] as $i => $item) {
                if (($item['image'] ?? '') === 'images/gallery-3.webp') {
                    $s['data']['items'][$i]['image_alt'] = 'An Amsterdam canal at blue hour with street lamps and lit canal houses reflected in the water';
                }
            }
        }
        unset($s);
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
