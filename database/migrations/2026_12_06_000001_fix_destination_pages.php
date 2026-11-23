<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Destination pages: proper capitals in the closing band, one name per place in the headings, and a "When to go" photo that fits.
 * The page content is stored in the database, so this updates existing sites; only values that still match the old defaults are
 * changed, so anything edited in the admin is left alone.
 */
return new class extends Migration
{
    public function up(): void
    {
        $names = DB::table('destinations')->pluck('name', 'slug');

        $whenPhotos = [
            'amsterdam-and-the-cities' => ['images/special_occasions.webp', 'images/journal-2.webp', 'A restaurant table set with drinks in a warm, candle-lit room', 'A quiet pier over calm water at dusk, with the Amsterdam skyline in the distance'],
            'tulips-and-countryside' => ['images/families.webp', 'images/gallery-2.webp', 'A traditional windmill beside a quiet canal at Kinderdijk', 'Bunches of colorful tulips ready to be sold at a flower stall'],
        ];
        $oldHeadings = ['Tours in Amsterdam and the cities', 'Tours in the tulips and countryside', 'Tours in Zeeland and the Delta'];

        foreach ($names as $slug => $name) {
            $page = DB::table('pages')->where('slug', 'dest-'.$slug)->first(['id', 'sections']);
            $sections = $page ? json_decode((string) $page->sections, true) : null;
            if (! is_array($sections)) {
                continue;
            }
            $changed = false;
            foreach ($sections as &$s) {
                $d = &$s['data'];
                switch ($s['type'] ?? '') {
                    case 'promo_band':
                        if (($d['heading'] ?? '') === 'Ready to plan your *'.mb_strtolower($name).' trip?*') {
                            $d['heading'] = 'Ready to plan your *'.$name.' trip?*';
                            $changed = true;
                        }
                        break;
                    case 'split_list':
                        if (($d['eyebrow'] ?? '') === 'Discover Amsterdam and the cities') {
                            $d['eyebrow'] = 'Discover Amsterdam & the Cities';
                            $changed = true;
                        }
                        break;
                    case 'tour_packages':
                        if (in_array($d['heading'] ?? '', $oldHeadings, true)) {
                            $d['heading'] = 'Tours in '.$name;
                            $changed = true;
                        }
                        break;
                    case 'cta_banner':
                        if (($d['heading'] ?? '') === 'Ready to explore Amsterdam and the cities?') {
                            $d['heading'] = 'Ready to explore Amsterdam & the Cities?';
                            $changed = true;
                        }
                        break;
                    case 'split_columns':
                        if (isset($whenPhotos[$slug]) && ($d['left_image'] ?? '') === $whenPhotos[$slug][0]) {
                            $d['left_image'] = $whenPhotos[$slug][1];
                            $d['left_image_alt'] = $whenPhotos[$slug][3];
                            $changed = true;
                        }
                        if ($slug === 'zeeland-and-the-delta' && ($d['left_image'] ?? '') === 'images/off_the_beaten_path.webp'
                            && ($d['left_image_alt'] ?? '') === 'A quiet Dutch harbor village with historic sailing boats in morning mist') {
                            $d['left_image_alt'] = 'An aerial view of Dutch polders, rivers and quiet villages';
                            $changed = true;
                        }
                        break;
                }
                unset($d);
            }
            unset($s);
            if ($changed) {
                DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($sections, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]);
            }
        }
    }

    public function down(): void {}
};
