<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Cover photos of the remaining packages now match each trip. Files are replaced in storage/app/public/media/2026 (sync with `npm run sync:media`); this updates the alt text. */
return new class extends Migration
{
    public function up(): void
    {
        $alts = [
            'dutch-highlights-chauffeur-tour' => 'A canal boat gliding past tall gabled houses on an Amsterdam canal',
            'windmills-tulips-cheese-chauffeur-tour' => 'A cheesemaker arranging wheels of Dutch cheese at a market stall',
            'zeeland-delta-chauffeur-tour' => 'A quiet Zeeland town square with a church tower and brick houses at dawn',
            'zeeland-coast-self-drive' => 'Sailing ships moored in a misty Zeeland harbor at sunrise',
            'cities-canals-small-group-tour' => 'A small group of travelers watching a canal boat pass on an Amsterdam canal',
            'netherlands-in-bloom-small-group-tour' => 'Rows of red and yellow tulips with a windmill at sunset',
            'windmills-waterways-small-group-tour' => 'A family cycling along a waterway past a traditional Dutch windmill',
        ];
        foreach ($alts as $slug => $alt) {
            DB::table('media')->where('path', 'media/2026/pkg-'.$slug.'-1')->update(['alt' => $alt]);
        }
    }
};
