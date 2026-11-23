<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Final self drive package covers (generated for the packages). Files live in storage/app/public/media/2026; this updates alt text. */
return new class extends Migration
{
    public function up(): void
    {
        $alts = [
            'tulip-route-self-drive' => 'A black car parked on a narrow road beside tulip fields, with two travelers beside it and a windmill in the distance',
            'amsterdam-historic-cities-self-drive' => 'A traveler leaning on a small silver car holding a map beside an Amsterdam canal lined with gabled brick houses',
            'zeeland-coast-self-drive' => 'A small silver car on a winding road through grassy dunes on the Zeeland coast, with a wind turbine and the sea behind',
        ];
        foreach ($alts as $slug => $alt) {
            DB::table('media')->where('path', 'media/2026/pkg-'.$slug.'-1')->update(['alt' => $alt]);
        }
    }
};
