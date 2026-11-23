<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Home Page New: hero, intro split, "Travel your way", gallery and closing banner use the project's own generated photos (public/images/nh-*.webp). */
return new class extends Migration
{
    public function up(): void
    {
        $page = DB::table('pages')->where('slug', 'home-page-new')->first(['id', 'sections']);
        $sections = $page ? json_decode((string) $page->sections, true) : null;
        if (! is_array($sections)) {
            return;
        }
        $strip = [
            ['nh-first-time', 'A canal boat passing tall gabled houses on an Amsterdam canal'],
            ['nh-couples', 'A couple dining at a canal-side table at dusk'],
            ['nh-families', 'A small group of travelers watching a canal boat go by'],
            ['nh-culture', 'A cheesemaker arranging wheels of Dutch cheese at a market stall'],
            ['nh-offpath', 'Sailing ships moored in a misty Dutch harbor at sunrise'],
            ['nh-occasions', 'A glass of champagne on a canal-side terrace at sunset'],
        ];
        foreach ($sections as &$s) {
            $d = &$s['data'];
            switch ($s['type'] ?? '') {
                case 'hero_centered':
                    $d['image'] = 'images/nh-hero.webp';
                    $d['image_alt'] = 'A family cycling along a waterway past a traditional Dutch windmill at golden hour';
                    break;
                case 'split_list':
                    $d['image1'] = 'images/nh-split-canal.webp';
                    $d['image1_alt'] = 'A quiet Dutch canal town at dawn with gabled brick houses reflected in still water';
                    $d['image2'] = 'images/nh-split-tulips.webp';
                    $d['image2_alt'] = 'A traditional windmill beside rows of tulips at golden hour';
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
                            ['caption' => 'Quiet town squares', 'image' => 'images/nh-town.webp', 'image_alt' => 'A quiet Dutch town square with a church tower at dawn'],
                            ['caption' => 'Tulip roads', 'image' => 'images/nh-drive.webp', 'image_alt' => 'A small car on a road between tulip fields'],
                            ['caption' => 'Misty windmills', 'image' => 'images/nh-mist-windmill.webp', 'image_alt' => 'A windmill beside a still canal in morning mist'],
                            ['caption' => 'Chauffeur service', 'image' => 'images/nh-chauffeur.webp', 'image_alt' => 'A chauffeur opening the door of a black van on a cobbled square'],
                            ['caption' => 'Canal-side walks', 'image' => 'images/nh-minibus.webp', 'image_alt' => 'Travelers walking beside a canal toward a minibus'],
                            ['caption' => 'Tulip markets', 'image' => 'images/gallery-2.webp', 'image_alt' => 'Bunches of colorful tulips wrapped in paper at a Dutch flower market'],
                        ];
                    }
                    break;
                case 'cta_banner':
                    $d['image'] = 'images/nh-cta.webp';
                    $d['image_alt'] = 'Rows of red and yellow tulips with a windmill on the horizon at sunset';
                    break;
            }
            unset($d);
        }
        unset($s);
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
