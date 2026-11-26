<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Photos of the three newer packages, chosen by file path (media IDs can differ between databases, which could give a package another package's cover).
 * The Utrecht, Delft & The Hague self drive gets the Utrecht canal photo as its cover, so it no longer matches another card.
 */
return new class extends Migration
{
    public function up(): void
    {
        $photos = [
            'utrecht-delft-the-hague-self-drive' => ['pkg-amsterdam-historic-cities-self-drive-2', 'pkg-amsterdam-historic-cities-self-drive-3', 'pkg-cities-canals-small-group-tour-2'],
            'alkmaar-edam-volendam-chauffeur-tour' => ['pkg-windmills-waterways-small-group-tour-2', 'pkg-windmills-tulips-cheese-chauffeur-tour-3', 'pkg-dutch-highlights-chauffeur-tour-3'],
            'zeeland-seaside-historic-towns-small-group-tour' => ['pkg-zeeland-coast-self-drive-2', 'pkg-zeeland-delta-chauffeur-tour-2', 'pkg-zeeland-coast-self-drive-3'],
        ];
        foreach ($photos as $slug => $files) {
            $package = DB::table('packages')->where('slug', $slug)->value('id');
            $media = array_map(fn ($f) => DB::table('media')->where('path', "media/2026/$f")->value('id'), $files);
            if (! $package || in_array(null, $media, true)) {
                continue;
            }
            DB::table('package_images')->where('package_id', $package)->delete();
            foreach ($media as $i => $m) {
                DB::table('package_images')->insert(['package_id' => $package, 'media_id' => $m, 'sort_order' => $i + 1]);
            }
        }
    }
};
