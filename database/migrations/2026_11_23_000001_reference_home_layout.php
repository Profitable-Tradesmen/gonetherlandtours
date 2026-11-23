<?php

use App\Support\DefaultSections;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** The home page exactly as the previous release seeded it (section types, in order). */
    private const OLD_ORDER = [
        'hero_video_quiz', 'ticker', 'split_list', 'packages_tabs', 'feature_band', 'usp_proof', 'tour_types', 'video_feature',
        'image_strip', 'steps', 'testimonials', 'gallery', 'text_cta', 'faq', 'blog_latest', 'cta_banner',
    ];

    /**
     * Reference redesign of the home page: hero, value strip, intro, journey cards, packages, "why us", proof ...
     * Only runs when the home page still has the previously seeded section order, so a home page an admin has re-arranged
     * is never touched. Every kept section keeps its id and the content the admin typed into it.
     */
    public function up(): void
    {
        $home = DB::table('pages')->where('slug', 'home')->first();
        $sections = $home ? json_decode((string) $home->sections, true) : null;
        if (! is_array($sections) || array_column($sections, 'type') !== self::OLD_ORDER) {
            return;
        }

        $by = array_column($sections, null, 'type');
        $new = [
            $by['hero_video_quiz'],
            DefaultSections::valueStrip(),
            $by['split_list'],
            $by['tour_types'],
            $by['packages_tabs'],
            DefaultSections::whyBand(),
            $by['usp_proof'],
            $by['video_feature'], $by['image_strip'], $by['steps'], $by['testimonials'], $by['gallery'], $by['text_cta'], $by['faq'], $by['blog_latest'], $by['cta_banner'],
        ];
        // the old ticker and photo band are not deleted: they stay available as hidden sections an admin can switch back on
        $ticker = $by['ticker'];
        $ticker['visible'] = false;
        $band = $by['feature_band'];
        $band['visible'] = false;
        $new[] = $ticker;
        $new[] = $band;

        DB::table('pages')->where('id', $home->id)->update(['sections' => json_encode($new, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }

    public function down(): void
    {
        // Layout change only: nothing to undo.
    }
};
