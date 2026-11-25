<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** New sharper photos: the images themselves are files; this updates the alt text of the two Chauffeur gallery photos. */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('media')->where('path', 'media/2026/pkg-dutch-highlights-chauffeur-tour-2')->update(['alt' => 'A couple walking along a country path beside the Zaanse Schans windmills at sunrise, with a dark minivan parked on the grass']);
        DB::table('media')->where('path', 'media/2026/pkg-windmills-tulips-cheese-chauffeur-tour-2')->update(['alt' => 'Two travellers in casual clothes stepping out of a minivan beside red and yellow tulip fields']);
    }
};
