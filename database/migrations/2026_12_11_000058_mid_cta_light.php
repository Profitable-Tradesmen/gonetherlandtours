<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** The mid-page call to action (after the destinations) becomes a light, centered block so it does not repeat the dark closing banner. */
return new class extends Migration
{
    public function up(): void
    {
        $page = DB::table('pages')->where('slug', 'home')->first(['id', 'sections']);
        $s = $page ? json_decode((string) $page->sections, true) : null;
        if (! is_array($s)) {
            return;
        }
        foreach ($s as $i => $x) {
            if (($x['type'] ?? '') === 'cta_banner' && ($x['data']['heading'] ?? '') === 'Tell us your dream. *We will plan the rest.*') {
                $s[$i] = ['id' => $x['id'], 'type' => 'text_cta', 'visible' => true, 'data' => [
                    'eyebrow' => 'Complimentary · No obligation',
                    'heading' => 'Tell us your dream. *We will plan the rest.*',
                    'text' => 'Share your dates and interests and a Netherlands specialist will send a tailored itinerary and quote, free of charge.',
                    'background' => 'white',
                    'first_button_gold' => true,
                    'buttons' => [['label' => 'Plan my journey', 'url' => ''], ['label' => 'View all packages', 'url' => '/packages']],
                ]];
            }
        }
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($s, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
