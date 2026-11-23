<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Final chauffeur driven package covers (generated for the packages). Files live in storage/app/public/media/2026; this updates alt text. */
return new class extends Migration
{
    public function up(): void
    {
        $alts = [
            'dutch-highlights-chauffeur-tour' => 'A chauffeur opening the door of a black Mercedes van for an older couple beside an Amsterdam canal',
            'windmills-tulips-cheese-chauffeur-tour' => 'A black van on a country road beside tulip fields and a windmill, with a chauffeur helping a guest step out',
            'zeeland-delta-chauffeur-tour' => 'A chauffeur holding the door of a black sedan for a couple at a misty Zeeland harbor',
        ];
        foreach ($alts as $slug => $alt) {
            DB::table('media')->where('path', 'media/2026/pkg-'.$slug.'-1')->update(['alt' => $alt]);
        }
    }
};
