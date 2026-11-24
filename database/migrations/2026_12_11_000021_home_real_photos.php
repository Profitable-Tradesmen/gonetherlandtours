<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Home page: the generated (AI) photos are replaced by the real stock photos already in public/images:
 * hero, intro split, "Travel your way", gallery and closing banner. The nh-*.webp files stay in public/images, unused.
 * Package and tour type photos are not touched here.
 */
return new class extends Migration
{
    public function up(): void
    {
        $page = DB::table('pages')->where('slug', 'home')->first(['id', 'sections']);
        $sections = $page ? json_decode((string) $page->sections, true) : null;
        if (! is_array($sections)) {
            return;
        }
        $strip = [
            ['first_time_visitors', 'Amsterdam canal houses and a canal boat on a bright morning'],
            ['couples', 'A Dutch canal lined with terraces and a boat gliding past historic houses'],
            ['families', 'A traditional windmill beside a quiet canal at Kinderdijk'],
            ['culture_lovers', 'A plate of Dutch cheese cubes for tasting'],
            ['off_the_beaten_path', 'An aerial view of Dutch polders, rivers and quiet villages'],
            ['special_occasions', 'A restaurant table set with drinks in a warm, candle-lit room'],
        ];
        foreach ($sections as &$s) {
            $d = &$s['data'];
            switch ($s['type'] ?? '') {
                case 'hero_centered':
                    $d['image'] = 'images/hero-home.webp';
                    $d['image_alt'] = 'Traditional Dutch windmills beside green polders under a blue sky';
                    break;
                case 'split_list':
                    $d['image1'] = 'images/Guide_1.webp';
                    $d['image1_alt'] = 'A quiet Dutch street of gabled brick houses beside a canal';
                    $d['image2'] = 'images/Intro_image_split1.webp';
                    $d['image2_alt'] = 'The De Adriaan windmill beside the Spaarne river in Haarlem';
                    break;
                case 'image_strip':
                    foreach ($strip as $i => [$file, $alt]) {
                        if (isset($d['items'][$i])) {
                            $d['items'][$i]['image'] = 'images/'.$file.'.webp';
                            $d['items'][$i]['image_alt'] = $alt;
                        }
                    }
                    break;
                case 'gallery':
                    if (($d['eyebrow'] ?? '') === 'The Netherlands collection') {
                        $d['items'] = [
                            ['caption' => 'Zaanse Schans windmills', 'image' => 'images/gallery-1.webp', 'image_alt' => 'Traditional windmills beside a green meadow at the Zaanse Schans'],
                            ['caption' => 'Tulip markets', 'image' => 'images/gallery-2.webp', 'image_alt' => 'Bunches of colorful tulips wrapped in paper at a Dutch flower market'],
                            ['caption' => 'Amsterdam after dark', 'image' => 'images/gallery-3.webp', 'image_alt' => 'A busy Amsterdam street at dusk with festive lights above the tram tracks'],
                            ['caption' => 'Canal-side bikes', 'image' => 'images/gallery-4.webp', 'image_alt' => 'Bicycles parked on a bridge beside an Amsterdam canal and tall brick houses'],
                            ['caption' => 'Flower stalls', 'image' => 'images/gallery-5.webp', 'image_alt' => 'A flower stall with buckets of tulips, hyacinths and spring blooms'],
                            ['caption' => 'Rotterdam', 'image' => 'images/gallery-6.webp', 'image_alt' => 'The tall white pylon of the Erasmus Bridge in Rotterdam against a blue sky'],
                        ];
                    }
                    break;
                case 'cta_banner':
                    $d['image'] = 'images/fina_cta_bg.webp';
                    $d['image_alt'] = 'Rows of red and yellow tulips in full bloom in a Dutch flower garden';
                    break;
            }
            unset($d);
        }
        unset($s);
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
