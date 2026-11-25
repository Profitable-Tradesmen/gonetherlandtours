<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Home: the featured packages become "Handpicked journeys" and move up, right after the intro, before the tour types (as on the sister sites). */
return new class extends Migration
{
    public function up(): void
    {
        $page = DB::table('pages')->where('slug', 'home')->first(['id', 'sections']);
        $sections = $page ? json_decode((string) $page->sections, true) : null;
        if (! is_array($sections)) {
            return;
        }
        $pkg = null;
        $rest = [];
        foreach ($sections as $s) {
            if (($s['type'] ?? '') === 'packages_tabs' && $pkg === null) {
                $s['data']['eyebrow'] = 'Featured journeys';
                $s['data']['heading'] = 'Handpicked *Netherlands journeys*';
                $s['data']['intro'] = 'Our most loved itineraries, planned by Netherlands specialists and fully customizable to your dates and interests.';
                $pkg = $s;
            } else {
                $rest[] = $s;
            }
        }
        if ($pkg === null) {
            return;
        }
        $at = count($rest);
        foreach ($rest as $i => $s) {
            if (($s['type'] ?? '') === 'split_list') {
                $at = $i + 1;
                break;
            }
        }
        array_splice($rest, $at, 0, [$pkg]);
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($rest, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
