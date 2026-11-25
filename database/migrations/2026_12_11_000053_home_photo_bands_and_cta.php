<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Home (sister-site structure): two full-width photo bands and a mid-page call to action. */
return new class extends Migration
{
    public function up(): void
    {
        $page = DB::table('pages')->where('slug', 'home')->first(['id', 'sections']);
        $s = $page ? json_decode((string) $page->sections, true) : null;
        if (! is_array($s) || in_array('photo_band', array_column($s, 'type'), true)) {
            return;
        }
        $band1 = ['id' => 'pb1'.substr(md5('band1'), 0, 5), 'type' => 'photo_band', 'visible' => true, 'data' => ['image' => 'images/feature_band.webp', 'image_alt' => 'A small green wooden house and an arched bridge reflected in a canal at Zaanse Schans at dusk']];
        $band2 = ['id' => 'pb2'.substr(md5('band2'), 0, 5), 'type' => 'photo_band', 'visible' => true, 'data' => ['image' => 'images/nh-cta.webp', 'image_alt' => 'Rows of red and yellow tulips running toward a windmill at sunrise']];
        $cta = ['id' => 'cta'.substr(md5('midcta'), 0, 5), 'type' => 'cta_banner', 'visible' => true, 'data' => [
            'eyebrow' => 'Complimentary · No obligation',
            'image' => 'images/cta-bg.webp',
            'image_alt' => 'An aerial view of green Dutch polders, canals and a quiet town',
            'heading' => 'Tell us your dream. *We will plan the rest.*',
            'text' => 'Share your dates and interests and a Netherlands specialist will send a tailored itinerary and quote, free of charge.',
            'button_label' => 'Plan my journey',
            'show_phone' => true,
            'phone_label' => 'Or call',
        ]];
        $out = [];
        foreach ($s as $x) {
            if (($x['type'] ?? '') === 'tour_types') {
                $out[] = $band1;
            }
            if (($x['type'] ?? '') === 'image_strip') {
                array_push($out, $band2, $cta);
            }
            $out[] = $x;
        }
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
