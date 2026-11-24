<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** The contact bar is now a sticky bar at the bottom of every desktop page (layouts/app), so the in-page bar is removed from the home page. */
return new class extends Migration
{
    public function up(): void
    {
        $page = DB::table('pages')->where('slug', 'home')->first(['id', 'sections']);
        $sections = $page ? json_decode((string) $page->sections, true) : null;
        if (! is_array($sections)) {
            return;
        }
        $out = array_values(array_filter($sections, fn ($s) => ($s['type'] ?? '') !== 'contact_bar'));
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
