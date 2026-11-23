<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The alt texts given to the three tour-type photos described scenes that are not in the pictures. They now describe what is there.
 * Only an alt text that still has the old wording is changed, so one edited in the admin is left alone.
 */
return new class extends Migration
{
    public function up(): void
    {
        $alts = [
            'self-drive' => [
                'A small car driving along a country road beside Dutch tulip fields and a windmill',
                'An aerial view of a winding country road with a small car, crossing green hills',
            ],
            'chauffeur-driven' => [
                'A chauffeur opening the door of a black car on a historic Dutch street',
                'The front of a black chauffeur-driven Mercedes sedan',
            ],
            'coach-tours' => [
                'A small tour group walking beside a minibus on a Dutch canal-side street',
                'A black executive minibus parked in the sun',
            ],
        ];
        foreach ($alts as $slug => [$old, $new]) {
            DB::table('tour_types')->where('slug', $slug)->where('image_alt', $old)->update(['image_alt' => $new]);
        }
    }

    public function down(): void {}
};
