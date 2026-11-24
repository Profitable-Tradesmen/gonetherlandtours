<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** The three tour type cards (home page, Tour Types menu, tour type and package fallbacks) use new aerial drone photos. */
return new class extends Migration
{
    public function up(): void
    {
        $photos = [
            'self-drive' => ['images/tour-self-drive-aerial.webp', 'Aerial view of a car parked on a country road beside striped tulip fields and a canal'],
            'chauffeur-driven' => ['images/tour-chauffeur-aerial.webp', 'Aerial view of a black van crossing a canal bridge between gabled Amsterdam canal houses'],
            'coach-tours' => ['images/tour-coach-aerial.webp', 'Aerial view of a white coach and a small group beside the Kinderdijk windmills and canals'],
        ];
        foreach ($photos as $slug => [$image, $alt]) {
            DB::table('tour_types')->where('slug', $slug)->update(['image' => $image, 'image_alt' => $alt]);
        }
    }
};
