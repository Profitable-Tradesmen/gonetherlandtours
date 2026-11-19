<?php

use App\Support\DefaultSections;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** The About banner is the home banner (with the quiz) over a photo; the separate quiz section is gone. Only a page still on the previous default layout is changed. */
    public function up(): void
    {
        $page = DB::table('pages')->where('slug', 'about')->first();
        if (! $page) {
            return;
        }
        $types = array_column(json_decode((string) $page->sections, true) ?: [], 'type');
        if ($types === ['hero_seasons', 'quiz_band', 'feature_cards', 'text_image', 'text_image', 'why_band', 'blog_latest', 'team', 'testimonials', 'steps', 'cta_banner']) {
            DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode(DefaultSections::about(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
        }
    }
};
