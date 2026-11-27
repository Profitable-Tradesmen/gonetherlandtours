<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Home page "Travel at your own pace": a stack of three postcards (quiet villages, canal towns, open polders). */
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
            if (($s['type'] ?? '') !== 'photo_band' || empty($s['data']['heading']) || ! empty($s['data']['photos'])) {
                continue;
            }
            $s['data']['photos'] = [
                ['image' => 'images/places/giethoorn.webp', 'image_alt' => 'A quiet waterway between gardens in Giethoorn', 'title' => 'Quiet villages', 'place' => 'Giethoorn'],
                ['image' => 'images/places/leiden.webp', 'image_alt' => 'A stone bridge over a tree-lined canal in Leiden', 'title' => 'Canal towns', 'place' => 'Leiden'],
                ['image' => 'images/places/kinderdijk.webp', 'image_alt' => 'Windmills along a reed-lined canal at Kinderdijk', 'title' => 'Open polders', 'place' => 'Kinderdijk'],
            ];
        }
        unset($s);
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
