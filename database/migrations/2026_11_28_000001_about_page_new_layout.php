<?php

use App\Support\DefaultSections;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** The About page got a new layout (feature cards, welcome text, why-us tiles, articles, 3 steps, closing banner). Only a page still on the old default layout is replaced; an edited page is left alone. */
    public function up(): void
    {
        $page = DB::table('pages')->where('slug', 'about')->first();
        if (! $page) {
            return;
        }
        $types = array_column(json_decode((string) $page->sections, true) ?: [], 'type');
        if ($types === ['hero_seasons', 'feature_cards', 'split_columns', 'why_band', 'team', 'testimonials', 'cta_banner']) {
            DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode(DefaultSections::about(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
        }
    }
};
