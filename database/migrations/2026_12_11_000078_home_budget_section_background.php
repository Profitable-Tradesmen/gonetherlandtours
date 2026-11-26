<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Home page: "Trips that fit your budget" gets a sand background so it no longer blends into the photo gallery above it. The guest
 * reviews move below it and turn white, so the sections alternate whether or not the reviews are shown. The budget grid also gets
 * its three-tour limit and matching text, which had only been set on one copy of the site.
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
        $budget = fn ($s) => ($s['type'] ?? '') === 'tour_packages' && ($s['data']['sort'] ?? '') === 'price';
        $reviews = null;
        foreach ($sections as $i => $s) {
            if (($s['type'] ?? '') === 'testimonials') {
                $reviews = array_splice($sections, $i, 1)[0];
                break;
            }
        }
        foreach ($sections as $i => &$s) {
            if (! $budget($s)) {
                continue;
            }
            $s['data']['background'] = 'sand';
            if (($s['data']['intro'] ?? '') === 'Lowest prices first, from short city breaks to a full week along the coast.') {
                $s['data']['intro'] = 'Our three lowest prices, from a short city break to a week along the coast.';
                $s['data']['limit'] = 3;
            }
            if ($reviews) {
                $reviews['data']['background'] = 'white';
                array_splice($sections, $i + 1, 0, [$reviews]);
                $reviews = null;
            }
            break;
        }
        unset($s);
        if ($reviews) {
            $sections[] = $reviews;   // no budget grid on this copy: keep the reviews on the page
        }
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
