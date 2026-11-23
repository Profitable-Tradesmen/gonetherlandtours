<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Package cover photos show the way of traveling: a car (self drive), a chauffeur and van (chauffeur driven), a minibus with guests (coach / small group). Files are replaced in storage/app/public/media/2026; this updates alt text. */
return new class extends Migration
{
    public function up(): void
    {
        $alts = [
            'zeeland-coast-self-drive' => 'A small car driving along a quiet polder road beside a canal',
            'dutch-highlights-chauffeur-tour' => 'A chauffeur in a dark suit opening the door of a black van on a cobbled Dutch square',
            'windmills-tulips-cheese-chauffeur-tour' => 'A chauffeur holding the door of a black van on a cobbled street in a Dutch town',
            'zeeland-delta-chauffeur-tour' => 'A van waiting beside a canal in a quiet Dutch village while guests walk ahead',
            'cities-canals-small-group-tour' => 'Travelers walking beside a canal toward their tour minibus, past planters of tulips',
            'netherlands-in-bloom-small-group-tour' => 'A tour minibus on a canal-side street lined with tulip planters, guests walking ahead',
            'windmills-waterways-small-group-tour' => 'A small group walking beside a tour minibus along a misty Dutch canal',
        ];
        foreach ($alts as $slug => $alt) {
            DB::table('media')->where('path', 'media/2026/pkg-'.$slug.'-1')->update(['alt' => $alt]);
        }
    }
};
