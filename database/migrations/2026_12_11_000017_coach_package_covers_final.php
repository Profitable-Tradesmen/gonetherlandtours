<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Final coach tour (small group) package covers (generated for the packages). Files live in storage/app/public/media/2026; this updates alt text. */
return new class extends Migration
{
    public function up(): void
    {
        $alts = [
            'cities-canals-small-group-tour' => 'A small group of travelers with a guide beside a white coach on an Amsterdam canal lined with gabled houses',
            'netherlands-in-bloom-small-group-tour' => 'A white coach on a country road beside tulip fields and a windmill at sunset, with travelers taking photos',
            'windmills-waterways-small-group-tour' => 'A white coach beside a Dutch waterway with a small group walking toward the windmills at Kinderdijk',
        ];
        foreach ($alts as $slug => $alt) {
            DB::table('media')->where('path', 'media/2026/pkg-'.$slug.'-1')->update(['alt' => $alt]);
        }
    }
};
