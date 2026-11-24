<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Home page declutter: real facts in the numbers band (instead of "3 ways" and "100%"), fewer repeated "Get a free quote" buttons
 * (numbers band and steps lose theirs), and the gallery section is removed (its photos repeat the hero and the traveler cards).
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
        $out = [];
        foreach ($sections as $s) {
            $type = $s['type'] ?? '';
            if ($type === 'gallery') {
                continue;
            }
            if ($type === 'stats_band') {
                $s['data']['items'] = [
                    ['value' => '19', 'label' => 'UNESCO windmills at Kinderdijk'],
                    ['value' => '7M', 'label' => 'Tulip bulbs planted at Keukenhof each year'],
                    ['value' => '165', 'label' => 'Canals in Amsterdam'],
                    ['value' => '35,000 km', 'label' => 'Of cycle paths across the country'],
                ];
                $s['data']['button_label'] = '';
            }
            if ($type === 'steps') {
                $s['data']['button_label'] = '';
            }
            $out[] = $s;
        }
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
