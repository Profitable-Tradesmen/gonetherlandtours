<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Home (sister-site structure): a second package grid after the destinations and a third after the gallery, each with its own wording. */
return new class extends Migration
{
    public function up(): void
    {
        $page = DB::table('pages')->where('slug', 'home')->first(['id', 'sections']);
        $s = $page ? json_decode((string) $page->sections, true) : null;
        if (! is_array($s) || count(array_filter($s, fn ($x) => ($x['type'] ?? '') === 'tour_packages'))) {
            return;
        }
        $grid = fn (string $id, array $data) => ['id' => $id, 'type' => 'tour_packages', 'visible' => true, 'data' => array_merge(['button_label' => 'Browse all tours', 'quote_label' => 'Get a free quote', 'empty_text' => 'No packages are published yet.'], $data)];
        $more = $grid('tpm'.substr(md5('more'), 0, 5), [
            'eyebrow' => 'More itineraries',
            'heading' => 'Routes travelers *book again and again*',
            'intro' => 'Six more tried-and-tested trips, each one open to change before you book.',
            'background' => 'white', 'sort' => 'default', 'offset' => 6, 'limit' => 6,
        ]);
        $fav = $grid('tpf'.substr(md5('fav'), 0, 5), [
            'eyebrow' => 'Great value',
            'heading' => 'Trips that *fit your budget*',
            'intro' => 'Lowest prices first, from short city breaks to a full week along the coast.',
            'background' => 'sand', 'sort' => 'price', 'offset' => 0, 'limit' => 6,
        ]);
        $out = [];
        foreach ($s as $x) {
            if (($x['type'] ?? '') === 'testimonials') {
                $out[] = $fav;
            }
            $out[] = $x;
            if (($x['type'] ?? '') === 'destination_cards') {
                $out[] = $more;
            }
        }
        if (! in_array($fav, $out, true)) {
            $out[] = $fav;
        }
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
