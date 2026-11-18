<?php

use App\Support\DestinationCopy;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fills the three seeded destinations with guide copy and fitting photo descriptions. Every field is replaced only while it still has
     * exactly the value an earlier release seeded, so anything an admin wrote is never touched. The photos themselves are the files
     * public/images/dest-*.webp (replaced by this release), so they change on their own.
     */
    public function up(): void
    {
        foreach (DestinationCopy::all() as $slug => $copy) {
            $row = DB::table('destinations')->where('slug', $slug)->first();
            if (! $row) {
                continue;
            }
            $update = [];
            if (str_contains((string) $row->body, DestinationCopy::PLACEHOLDER_MARK)) {
                $update['body'] = $copy['body'];
            }
            if ($row->h1 === $row->name.' Tours') {
                $update['h1'] = $copy['h1'];
            }
            if ($row->seo_title === $row->name.' Tours | GoNetherlandTours') {
                $update['seo_title'] = $copy['seo_title'];
            }
            if ($row->meta_description === 'Explore '.$row->name.' with GoNetherlandTours. '.$row->summary) {
                $update['meta_description'] = $copy['meta_description'];
            }
            if ($row->summary === DestinationCopy::OLD_SUMMARY_ZEELAND) {
                $update['summary'] = $copy['summary'];
            }
            if ($row->image_alt === DestinationCopy::OLD_ALT[$slug]) {
                $update['image_alt'] = $copy['image_alt'];
            }
            if ($update) {
                DB::table('destinations')->where('slug', $slug)->update($update + ['updated_at' => now()]);
            }
        }

        // the intro of the Destinations page said "Six places" although there are three regions
        DB::table('settings')->where('key', 'destinations_intro')->where('value', 'Six places worth slowing down for: pick a region and see the tours that visit it.')
            ->update(['value' => 'Regions worth slowing down for: pick one and see the tours that visit it.']);
    }

    public function down(): void
    {
        // Content change only: nothing to undo.
    }
};
