<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Cover photos of the two self drive packages now show the trip: a car on a road through tulip fields (Tulip Route) and a person beside
 * the car on a historic Amsterdam street (Amsterdam & Historic Cities). The image files are replaced in storage/app/public/media/2026
 * (run `npm run sync:media` to copy them to the server); this migration updates the alt text.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('media')->where('path', 'media/2026/pkg-tulip-route-self-drive-1')
            ->update(['alt' => 'A small car driving along a quiet road between Dutch tulip fields with a windmill in the distance']);
        DB::table('media')->where('path', 'media/2026/pkg-amsterdam-historic-cities-self-drive-1')
            ->update(['alt' => 'A traveler standing beside a car on a cobbled street lined with historic Amsterdam canal houses']);
    }
};
