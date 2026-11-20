<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Point the three tour types at the new Self Drive / Chauffeur / Coach photos (uploaded to storage/app/public/media/2026). */
    public function up(): void
    {
        $photos = [
            'self-drive' => ['storage/media/2026/bkhahmxqvg5hpu-960.webp', 'A small car driving along a country road beside Dutch tulip fields and a windmill'],
            'chauffeur-driven' => ['storage/media/2026/si2kwqzr9sahez-960.webp', 'A chauffeur opening the door of a black car on a historic Dutch street'],
            'coach-tours' => ['storage/media/2026/fieujyz6zribuo-960.webp', 'A small tour group walking beside a minibus on a Dutch canal-side street'],
        ];
        foreach ($photos as $slug => [$image, $alt]) {
            DB::table('tour_types')->where('slug', $slug)->update(['image' => $image, 'image_alt' => $alt]);
        }
    }

    public function down(): void
    {
    }
};
