<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Destination pages (slugs dest-*): the "When to go / How you can travel" two-column section is removed. */
return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('pages')->where('slug', 'like', 'dest-%')->get(['id', 'sections']) as $page) {
            $sections = json_decode((string) $page->sections, true);
            if (! is_array($sections)) {
                continue;
            }
            $kept = array_values(array_filter($sections, fn ($s) => ($s['type'] ?? '') !== 'split_columns'));
            if (count($kept) !== count($sections)) {
                DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($kept, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
            }
        }
    }
};
