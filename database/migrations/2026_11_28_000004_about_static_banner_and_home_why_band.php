<?php

use App\Support\DefaultSections;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** About: static photo banner (no quiz) and the home page's "Why choose us" section. Only a page still on the previous default layout is replaced. */
    public function up(): void
    {
        $page = DB::table('pages')->where('slug', 'about')->first();
        if (! $page) {
            return;
        }
        $types = array_column(json_decode((string) $page->sections, true) ?: [], 'type');
        if ($types === ['hero_video_quiz', 'feature_cards', 'text_image', 'text_image', 'why_band', 'blog_latest', 'team', 'testimonials', 'steps', 'cta_banner']) {
            DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode(DefaultSections::about(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
        }
    }
};
