<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Home Page New: "Travel with confidence" becomes a light, white numbered-columns section so it no longer looks like the photo call to action after it. */
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
            if (($s['type'] ?? '') === 'why_band' && ($s['data']['eyebrow'] ?? '') === 'Travel with confidence') {
                $s['type'] = 'steps';
                $s['data'] = [
                    'style' => 'cards', 'eyebrow' => 'Travel with confidence', 'heading' => 'Planning made *simple and secure*',
                    'intro' => 'Know what you are paying for and when, before you commit.', 'button_label' => 'Get a free quote',
                    'items' => [
                        ['title' => 'Clear quotes in euros', 'text' => 'Know what is included before you decide, with no hidden fees.'],
                        ['title' => 'Simple payment schedule', 'text' => 'A deposit when you book and the balance 60 days before departure.'],
                        ['title' => 'Confirmed in writing', 'text' => 'Your itinerary and price are confirmed in writing before you travel.'],
                        ['title' => 'Support along the way', 'text' => 'A real person to help before you leave and while you travel.'],
                    ],
                ];
            }
        }
        unset($s);
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
