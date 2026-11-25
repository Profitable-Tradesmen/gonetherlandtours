<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Home: a photo gallery of six Dutch places, after "Your journey. Your way." and before the reviews (as on the sister site). */
return new class extends Migration
{
    public function up(): void
    {
        $page = DB::table('pages')->where('slug', 'home')->first(['id', 'sections']);
        $s = $page ? json_decode((string) $page->sections, true) : null;
        if (! is_array($s) || in_array('gallery', array_column($s, 'type'), true)) {
            return;
        }
        $item = fn ($caption, $image, $alt) => ['caption' => $caption, 'image' => $image, 'image_alt' => $alt];
        $gallery = ['id' => 'gal'.substr(md5('home-gallery'), 0, 5), 'type' => 'gallery', 'visible' => true, 'data' => [
            'eyebrow' => 'The Netherlands gallery',
            'heading' => 'The Netherlands’ finest places, *beautifully curated*',
            'items' => [
                $item('Zaanse Schans', 'images/gallery-1.webp', 'Traditional wooden windmills beside a green meadow at Zaanse Schans'),
                $item('Tulip season', 'images/gallery-2.webp', 'Bunches of colorful tulips wrapped in paper'),
                $item('Amsterdam canals', 'images/gallery-3.webp', 'An Amsterdam canal lined with historic houses and lit up at dusk'),
                $item('Haarlem', 'images/Guide_2.webp', 'The Grote Markt in Haarlem with the Sint-Bavo church and café terraces'),
                $item('Flower markets', 'images/gallery-5.webp', 'Fresh flowers and tulips for sale at an Amsterdam flower market'),
                $item('Rotterdam', 'images/gallery-6.webp', 'The tall white pylon of the Erasmus Bridge against a blue sky'),
            ],
            'closing_heading' => 'Seen enough to start planning?',
            'button_label' => 'Plan my journey',
        ]];
        $at = count($s);
        foreach ($s as $i => $x) {
            if (($x['type'] ?? '') === 'testimonials') {
                $at = $i;
                break;
            }
        }
        array_splice($s, $at, 0, [$gallery]);
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($s, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
