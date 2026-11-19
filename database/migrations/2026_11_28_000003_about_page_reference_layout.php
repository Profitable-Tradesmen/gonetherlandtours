<?php

use App\Support\DefaultSections;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** The About page now follows the reference layout. A page still on one of the earlier default layouts is replaced; an edited page is left alone. */
    private const OLD = [
        ['hero_seasons', 'feature_cards', 'split_columns', 'why_band', 'team', 'testimonials', 'cta_banner'],
        ['hero_seasons', 'feature_cards', 'text_cta', 'split_columns', 'why_band', 'blog_latest', 'team', 'testimonials', 'steps', 'cta_banner'],
    ];

    public function up(): void
    {
        $page = DB::table('pages')->where('slug', 'about')->first();
        if (! $page) {
            return;
        }
        $types = array_column(json_decode((string) $page->sections, true) ?: [], 'type');
        if (in_array($types, self::OLD, true)) {
            DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode(DefaultSections::about(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
        }
    }
};
