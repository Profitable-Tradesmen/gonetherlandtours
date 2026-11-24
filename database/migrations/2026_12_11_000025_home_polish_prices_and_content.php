<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Content polish:
 *  - Placeholder "from" prices (per person, double occupancy, EUR) set in line with what comparable Netherlands tours are listed at
 *    (self drive about 1.5-2.0k, private chauffeur 2.5-3.5k, small group 2.6-3.3k). They are placeholders until the real rates are set in Admin > Packages.
 *  - Home page: "Our Best Trusted Partners" heading shortened; the sample reviews section is hidden until real reviews exist;
 *    the intro photo and the last gallery tile are swapped for better ones.
 */
return new class extends Migration
{
    public function up(): void
    {
        $prices = [
            'tulip-route-self-drive' => 1790,
            'amsterdam-historic-cities-self-drive' => 1990,
            'zeeland-coast-self-drive' => 1490,
            'dutch-highlights-chauffeur-tour' => 2990,
            'windmills-tulips-cheese-chauffeur-tour' => 2490,
            'zeeland-delta-chauffeur-tour' => 3490,
            'netherlands-in-bloom-small-group-tour' => 2990,
            'cities-canals-small-group-tour' => 3290,
            'windmills-waterways-small-group-tour' => 2590,
        ];
        foreach ($prices as $slug => $price) {
            DB::table('packages')->where('slug', $slug)->update(['price_from' => $price]);
        }

        $page = DB::table('pages')->where('slug', 'home')->first(['id', 'sections']);
        $sections = $page ? json_decode((string) $page->sections, true) : null;
        if (! is_array($sections)) {
            return;
        }
        foreach ($sections as &$s) {
            $d = &$s['data'];
            switch ($s['type'] ?? '') {
                case 'partners':
                    $d['heading'] = 'Our trusted *partners*';
                    break;
                case 'testimonials':
                    $s['visible'] = false;   // shows "Sample review" cards; switch back on in Admin > Pages once real reviews are added
                    break;
                case 'split_list':
                    $d['image1'] = 'images/Guide_2.webp';
                    $d['image1_alt'] = 'The stepped-gable facade of a historic Dutch town hall on a sunny day';
                    break;
                case 'gallery':
                    if (($d['eyebrow'] ?? '') === 'The Netherlands collection' && isset($d['items'][5])) {
                        $d['items'][5] = ['caption' => 'Village at dusk', 'image' => 'images/feature_band.webp', 'image_alt' => 'Green wooden houses and a footbridge reflected in still water at dusk in a Dutch village'];
                    }
                    break;
            }
            unset($d);
        }
        unset($s);
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
