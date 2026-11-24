<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Final cover photos for the Zeeland Coast self drive, the three chauffeur packages and the three coach packages. Files are in storage/app/public/media/2026; this updates the alt text. */
return new class extends Migration
{
    public function up(): void
    {
        $alts = [
            'zeeland-coast-self-drive' => 'A white hatchback on a winding road through grassy dunes on the Zeeland coast, with the North Sea and a wind turbine behind',
            'dutch-highlights-chauffeur-tour' => 'A chauffeur opening the door of a black minivan for an older couple beside an Amsterdam canal with gabled houses and a stone bridge',
            'windmills-tulips-cheese-chauffeur-tour' => 'A chauffeur helping a guest out of a black minivan on a country road beside tulip fields and a windmill, while another guest takes a photo',
            'zeeland-delta-chauffeur-tour' => 'A chauffeur holding the door of a black sedan for a couple on a misty harbor quay in Zeeland, with moored sailing boats and brick houses',
            'cities-canals-small-group-tour' => 'A guide talking to a small group of travelers beside a white coach on an Amsterdam canal quay lined with gabled houses',
            'netherlands-in-bloom-small-group-tour' => 'A white coach on a country road beside tulip fields, with a small group of travelers walking and taking photos and a windmill on the horizon',
            'windmills-waterways-small-group-tour' => 'A white coach beside a Dutch canal, with a small group walking along the path toward a row of historic windmills',
        ];
        foreach ($alts as $slug => $alt) {
            DB::table('media')->where('path', 'media/2026/pkg-'.$slug.'-1')->update(['alt' => $alt]);
        }
    }
};
