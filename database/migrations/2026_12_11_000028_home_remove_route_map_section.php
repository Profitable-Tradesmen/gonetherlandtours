<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Home page: the "Route map" section is removed (the Netherlands outline is now a faint background of the featured packages instead). */
return new class extends Migration
{
    public function up(): void
    {
        $page = DB::table('pages')->where('slug', 'home')->first(['id', 'sections']);
        $sections = $page ? json_decode((string) $page->sections, true) : null;
        if (! is_array($sections)) {
            return;
        }
        $kept = array_values(array_filter($sections, fn ($s) => ($s['type'] ?? '') !== 'route_map'));
        if (count($kept) !== count($sections)) {
            DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($kept, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
        }
    }
};
