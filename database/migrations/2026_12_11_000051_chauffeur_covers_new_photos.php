<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** The new Zaanse Schans and tulip-field photos are now the cover (card) photos of two Chauffeur packages; the old covers move to gallery photo 2. */
return new class extends Migration
{
    public function up(): void
    {
        $a = ['pkg-dutch-highlights-chauffeur-tour', 'A couple walking along a country path beside the Zaanse Schans windmills at sunrise, with a dark minivan parked on the grass', 'A black minivan parked beside an Amsterdam canal while two travellers in casual clothes look out over the water'];
        $b = ['pkg-windmills-tulips-cheese-chauffeur-tour', 'Two travellers in casual clothes stepping out of a minivan beside red and yellow tulip fields', 'A black minivan on a country lane beside red and yellow tulip fields and a windmill, with two travellers taking photos'];
        foreach ([$a, $b] as [$slug, $newAlt, $oldAlt]) {
            DB::table('media')->where('path', "media/2026/$slug-1")->update(['alt' => $newAlt]);
            DB::table('media')->where('path', "media/2026/$slug-2")->update(['alt' => $oldAlt]);
        }
    }
};
