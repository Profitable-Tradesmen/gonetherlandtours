<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Home Page New, three fixes:
 *  - hero chips only point where they say: the three regions, the three ways to travel and all packages (no more Haarlem/Delft/Utrecht all opening Amsterdam);
 *  - the numbers band shows facts about the Netherlands and the offer (windmills, tulip bulbs, ways to travel, free quotes) instead of 3 / 3 / 1;
 *  - the photo gallery uses six photos that appear nowhere else on the page.
 */
return new class extends Migration
{
    public function up(): void
    {
        $page = DB::table('pages')->where('slug', 'home-page-new')->first(['id', 'sections']);
        $sections = $page ? json_decode((string) $page->sections, true) : null;
        if (! is_array($sections)) {
            return;
        }
        foreach ($sections as &$s) {
            $d = &$s['data'];
            switch ($s['type'] ?? '') {
                case 'hero_centered':
                    $d['chips'] = [
                        ['label' => 'Amsterdam & the Cities', 'url' => '/destinations/amsterdam-and-the-cities'],
                        ['label' => 'Tulips & Countryside', 'url' => '/destinations/tulips-and-countryside'],
                        ['label' => 'Zeeland & the Delta', 'url' => '/destinations/zeeland-and-the-delta'],
                        ['label' => 'Self Drive', 'url' => '/tours/self-drive'],
                        ['label' => 'Chauffeur Driven', 'url' => '/tours/chauffeur-driven'],
                        ['label' => 'Coach Tours', 'url' => '/tours/coach-tours'],
                        ['label' => 'All Packages', 'url' => '/packages'],
                    ];
                    break;
                case 'stats_band':
                    if (($d['eyebrow'] ?? '') === 'The GoNetherlandTours difference') {
                        $d['eyebrow'] = 'The Netherlands in numbers';
                        $d['heading'] = 'A small country with *big experiences*';
                        $d['text'] = 'Windmills, tulip fields and canal towns are all within easy reach of each other, so you see more and rush less.';
                        $d['items'] = [
                            ['value' => '19', 'label' => 'UNESCO windmills at Kinderdijk'],
                            ['value' => '7M', 'label' => 'Tulip bulbs planted at Keukenhof each year'],
                            ['value' => '3', 'label' => 'Ways to travel with us'],
                            ['value' => '100%', 'label' => 'Free, no-obligation quotes'],
                        ];
                    }
                    break;
                case 'gallery':
                    if (($d['eyebrow'] ?? '') === 'The Netherlands collection') {
                        $d['items'] = [
                            ['caption' => 'Tulip markets', 'image' => 'images/gallery-2.webp', 'image_alt' => 'Bunches of colorful tulips wrapped in paper at a Dutch flower market'],
                            ['caption' => 'Amsterdam after dark', 'image' => 'images/gallery-3.webp', 'image_alt' => 'A busy Amsterdam street at dusk with festive lights above the tram tracks'],
                            ['caption' => 'Haarlem windmill', 'image' => 'images/hero.webp', 'image_alt' => 'The De Adriaan windmill beside the Spaarne river in Haarlem'],
                            ['caption' => 'Canal-side bikes', 'image' => 'images/gallery-4.webp', 'image_alt' => 'Bicycles parked on a bridge beside an Amsterdam canal and tall brick houses'],
                            ['caption' => 'Flower stalls', 'image' => 'images/gallery-5.webp', 'image_alt' => 'A flower stall with buckets of tulips, hyacinths and spring blooms'],
                            ['caption' => 'Rotterdam', 'image' => 'images/gallery-6.webp', 'image_alt' => 'The tall white pylon of the Erasmus Bridge in Rotterdam against a blue sky'],
                        ];
                    }
                    break;
            }
            unset($d);
        }
        unset($s);
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
