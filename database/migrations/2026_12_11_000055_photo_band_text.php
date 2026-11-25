<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** The two home photo bands get a short line of text and a button on the photo, so they do not look empty. */
return new class extends Migration
{
    public function up(): void
    {
        $page = DB::table('pages')->where('slug', 'home')->first(['id', 'sections']);
        $s = $page ? json_decode((string) $page->sections, true) : null;
        if (! is_array($s)) {
            return;
        }
        $text = [
            'images/feature_band.webp' => ['Never rushed', 'Travel at *your own pace*', 'Quiet villages, canal towns and open polders, planned around how you like to travel.', 'Plan my journey'],
            'images/nh-cta.webp' => ['Spring is the busiest season', 'Tulip season fills up *early*', 'Book your dates now and we will plan the rest, free and with no obligation.', 'Get a free quote'],
        ];
        foreach ($s as &$x) {
            if (($x['type'] ?? '') === 'photo_band' && isset($text[$x['data']['image'] ?? ''])) {
                [$e, $h, $t, $b] = $text[$x['data']['image']];
                $x['data'] = array_merge($x['data'], ['eyebrow' => $e, 'heading' => $h, 'text' => $t, 'button_label' => $b]);
            }
        }
        unset($x);
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($s, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
