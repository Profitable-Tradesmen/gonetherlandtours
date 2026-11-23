<?php

use App\Support\DefaultSections;
use App\Support\UsSpelling;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** table => [plain/HTML text columns, JSON columns] converted to US spelling. Never touches slugs, keys, values, links or image paths. */
    private const TEXT = [
        'pages' => [['title', 'h1', 'seo_title', 'meta_description', 'body'], ['content', 'sections']],
        'faqs' => [['question', 'answer'], []],
        'testimonials' => [['quote'], []],
        'blog_posts' => [['title', 'excerpt', 'body', 'label', 'image_alt', 'seo_title', 'meta_description'], []],
        'packages' => [['title', 'summary', 'description', 'seo_title', 'meta_description'], ['highlights', 'itinerary', 'inclusions', 'exclusions', 'faqs']],
        'tour_types' => [['summary', 'h1', 'body', 'image_alt', 'seo_title', 'meta_description'], []],
        'destinations' => [['summary', 'h1', 'body', 'image_alt', 'seo_title', 'meta_description'], []],
        'quiz_steps' => [['question'], []],
        'quiz_options' => [['label'], []],
    ];

    /** Home page wording made accurate: only coach tours are small-group; self drive and chauffeur tours are private. */
    private const OLD_HERO = 'Windmills, tulip fields, cheese markets and hidden gems, on small-group and tailor-made tours with expertly crafted itineraries. Answer a few quick questions and get a free quote.';

    public function up(): void
    {
        // 1. A "travel style" note per tour type (shown on package and tour type pages).
        if (! Schema::hasColumn('tour_types', 'style_note')) {
            Schema::table('tour_types', function (Blueprint $t) {
                $t->string('style_note', 160)->nullable();
            });
        }
        foreach ([
            'self-drive' => 'Private: just you and your own party, at your own pace',
            'chauffeur-driven' => 'Private: your own party with a local driver-guide',
            'coach-tours' => 'Small group with a guide',
        ] as $slug => $note) {
            DB::table('tour_types')->where('slug', $slug)->whereNull('style_note')->update(['style_note' => $note]);
        }

        // 2. US spelling for everything already stored.
        foreach (self::TEXT as $table => [$text, $json]) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            foreach (DB::table($table)->get() as $row) {
                $update = [];
                foreach ($text as $col) {
                    if (Schema::hasColumn($table, $col) && is_string($row->$col ?? null)) {
                        $new = UsSpelling::convert($row->$col);
                        if ($new !== $row->$col) {
                            $update[$col] = $new;
                        }
                    }
                }
                foreach ($json as $col) {
                    if (Schema::hasColumn($table, $col) && is_string($row->$col ?? null) && $row->$col !== '') {
                        $decoded = json_decode($row->$col, true);
                        if (is_array($decoded)) {
                            $new = json_encode(UsSpelling::deep($decoded), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                            if ($new !== json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) || $new !== $row->$col) {
                                $update[$col] = $new;
                            }
                        }
                    }
                }
                if ($update) {
                    DB::table($table)->where('id', $row->id)->update($update);
                }
            }
        }
        // Site settings (never the secrets or CRM credentials).
        foreach (DB::table('settings')->get() as $s) {
            if (is_string($s->value) && ! preg_match('/token|secret|key|ghl|recaptcha|logo|favicon|image|gtm|email|phone/i', $s->key)) {
                $new = UsSpelling::convert($s->value);
                if ($new !== $s->value) {
                    DB::table('settings')->where('key', $s->key)->update(['value' => $new]);
                }
            }
        }

        // 3. Home page: accurate group-size wording + the "selling points with proof" section.
        $home = DB::table('pages')->where('slug', 'home')->first();
        if ($home) {
            $update = [];
            $map = [
                'seo_title' => ['Authentic Netherlands Tours | Small Group, Self Drive & Chauffeur | GoNetherlandTours', 'Netherlands Tours: Self Drive, Chauffeur & Small-Group Coach | GoNetherlandTours'],
                'meta_description' => ['Small-group and tailor-made Netherlands tours for US travelers: windmills, tulip fields, cheese markets and hidden gems, with expertly crafted itineraries. Get a free quote.', 'Private self drive and chauffeur tours or small-group coach tours of the Netherlands for US travelers: windmills, tulip fields, cheese markets and hidden gems, with expertly crafted itineraries. Get a free quote.'],
            ];
            foreach ($map as $col => [$old, $new]) {
                if ($home->$col === $old) {
                    $update[$col] = $new;
                }
            }

            $sections = json_decode((string) $home->sections, true);
            if (is_array($sections)) {
                $hasProof = false;
                $heroNew = 'Windmills, tulip fields, cheese markets and hidden gems, on private self drive and chauffeur tours or small-group coach tours, with expertly crafted itineraries. Answer a few quick questions and get a free quote.';
                foreach ($sections as &$s) {
                    $d = &$s['data'];
                    $type = $s['type'] ?? '';
                    $hasProof = $hasProof || $type === 'usp_proof';
                    if ($type === 'hero_video_quiz') {
                        if (($d['subheading'] ?? '') === self::OLD_HERO) {
                            $d['subheading'] = $heroNew;
                        }
                        foreach ($d['badges'] ?? [] as $i => $b) {
                            if (($b['text'] ?? '') === 'Small group experiences') {
                                $d['badges'][$i]['text'] = 'Small-group & private tours';
                            }
                        }
                    }
                    if ($type === 'split_list') {
                        foreach ($d['items'] ?? [] as $i => $it) {
                            if (($it['title'] ?? '') === 'Small Group Experiences' && ($it['text'] ?? '') === 'Enjoy more personal attention, flexible itineraries and meaningful connections with fellow travelers.') {
                                $d['items'][$i]['text'] = 'On our coach tours you travel in a small group; on self drive and chauffeur tours it is just your own party. Either way, expect personal attention, flexible itineraries and meaningful connections.';
                            }
                        }
                    }
                    if ($type === 'ticker') {
                        foreach ($d['items'] ?? [] as $i => $it) {
                            if (($it['text'] ?? '') === 'Small Group Experiences') {
                                $d['items'][$i]['text'] = 'Small-Group & Private Tours';
                            }
                        }
                    }
                    if ($type === 'feature_band') {
                        foreach ($d['bullets'] ?? [] as $i => $it) {
                            if (($it['text'] ?? '') === 'Small groups with flexible itineraries') {
                                $d['bullets'][$i]['text'] = 'Small-group coach tours and private options';
                            }
                        }
                    }
                    if ($type === 'text_cta' && isset($d['text']) && str_contains($d['text'], 'in small groups, with room to explore')) {
                        $d['text'] = str_replace('in small groups, with room to explore', 'in small groups or privately, with room to explore', $d['text']);
                    }
                    unset($d);
                }
                unset($s);

                if (! $hasProof) {
                    $at = collect($sections)->search(fn ($x) => ($x['type'] ?? '') === 'feature_band');
                    array_splice($sections, $at === false ? count($sections) : $at + 1, 0, [DefaultSections::uspProof()]);
                }
                $update['sections'] = json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
            if ($update) {
                DB::table('pages')->where('id', $home->id)->update($update);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('tour_types', 'style_note')) {
            Schema::table('tour_types', function (Blueprint $t) {
                $t->dropColumn('style_note');
            });
        }
    }
};
