<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tour type cards, the mega menu and the tour pages use the generated photos (public/images/tour-*-card.webp, with a wide
 * tour-*-hero.webp copy for the page banner) instead of the older admin uploads: a car at the tulip fields, a chauffeur with a van,
 * a coach with a guided group.
 */
return new class extends Migration
{
    public function up(): void
    {
        $rows = [
            'self-drive' => ['images/tour-self-drive-card.webp', 'A small car parked on a narrow road beside tulip fields, with two travelers and a windmill in the distance'],
            'chauffeur-driven' => ['images/tour-chauffeur-card.webp', 'A chauffeur opening the door of a black van for a couple beside an Amsterdam canal'],
            'coach-tours' => ['images/tour-coach-card.webp', 'A white coach on an Amsterdam canal street with a guide and a small group of travelers'],
        ];
        foreach ($rows as $slug => [$image, $alt]) {
            DB::table('tour_types')->where('slug', $slug)->update(['image' => $image, 'image_alt' => $alt]);
        }
    }
};
