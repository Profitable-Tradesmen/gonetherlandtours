<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Home Page New: adds the home page's "How It Works" (connected numbered timeline) and "Travel partners" (dark band with logos) after the
 * reviews, and moves "Travel with confidence" after them. Content is copied from the current home page, so edits there carry over.
 */
return new class extends Migration
{
    public function up(): void
    {
        $new = DB::table('pages')->where('slug', 'home-page-new')->first(['id', 'sections']);
        $home = DB::table('pages')->where('slug', 'home')->first(['sections']);
        $sections = $new ? json_decode((string) $new->sections, true) : null;
        $homeSections = $home ? json_decode((string) $home->sections, true) : null;
        if (! is_array($sections) || ! is_array($homeSections)) {
            return;
        }
        $types = array_column($sections, 'type');
        if (in_array('partners', $types, true)) {
            return;
        }
        $from = fn (string $type) => collect($homeSections)->firstWhere('type', $type);

        $steps = $from('steps');
        $partners = $from('partners');
        $add = [];
        if ($steps) {
            $add[] = ['id' => Str::lower(Str::random(8)), 'type' => 'steps', 'visible' => true, 'data' => array_merge($steps['data'], ['style' => 'timeline'])];
        }
        if ($partners) {
            $add[] = ['id' => Str::lower(Str::random(8)), 'type' => 'partners', 'visible' => true, 'data' => $partners['data']];
        }

        // Put them after the reviews, then the "Travel with confidence" section, then the call to action.
        $at = collect($sections)->search(fn ($s) => ($s['type'] ?? '') === 'testimonials');
        array_splice($sections, $at === false ? count($sections) - 2 : $at + 1, 0, $add);

        DB::table('pages')->where('id', $new->id)->update(['sections' => json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
