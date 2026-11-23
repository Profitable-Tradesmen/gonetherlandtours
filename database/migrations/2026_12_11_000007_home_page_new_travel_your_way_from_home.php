<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Home Page New: "Travel your way" shows the home page's "Journeys by traveler" panels (all six, with the same photos and button), under its own heading. */
return new class extends Migration
{
    public function up(): void
    {
        $new = DB::table('pages')->where('slug', 'home-page-new')->first(['id', 'sections']);
        $home = DB::table('pages')->where('slug', 'home')->first(['sections']);
        $sections = $new ? json_decode((string) $new->sections, true) : null;
        $homeStrip = $home ? collect(json_decode((string) $home->sections, true) ?: [])->firstWhere('type', 'image_strip') : null;
        if (! is_array($sections) || ! $homeStrip) {
            return;
        }
        foreach ($sections as &$s) {
            if (($s['type'] ?? '') === 'image_strip' && ($s['data']['eyebrow'] ?? '') === 'Travel your way') {
                $s['data']['items'] = $homeStrip['data']['items'];
                $s['data']['button_label'] = $homeStrip['data']['button_label'] ?? $s['data']['button_label'];
            }
        }
        unset($s);
        DB::table('pages')->where('id', $new->id)->update(['sections' => json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
