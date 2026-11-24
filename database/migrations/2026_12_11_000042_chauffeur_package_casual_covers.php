<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Chauffeur Driven package covers: new relaxed photos (casual travellers, no one in suits); the image files are replaced, this updates the alt text. */
return new class extends Migration
{
    public function up(): void
    {
        $alts = [
            'dutch-highlights-chauffeur-tour' => 'A black minivan parked beside an Amsterdam canal while two travellers in casual clothes look out over the water',
            'windmills-tulips-cheese-chauffeur-tour' => 'A black minivan on a country lane beside red and yellow tulip fields and a windmill, with two travellers taking photos',
            'zeeland-delta-chauffeur-tour' => 'A black minivan beside a small Zeeland harbour with sailing boats, travellers chatting and two people having coffee on a bench',
        ];
        foreach ($alts as $slug => $alt) {
            DB::table('media')->where('path', 'media/2026/pkg-'.$slug.'-1')->update(['alt' => $alt]);
        }
    }
};
