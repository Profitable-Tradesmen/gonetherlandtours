<?php

use App\Support\FaqCopy;
use App\Support\UsSpelling;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Site copy for travelers from the US and Canada: US spelling and vocabulary everywhere (customize, vacation, fall, custom instead of
 * tailor-made, car rental), "American and Canadian travelers", entry/driving/currency/insurance FAQs for both countries, and Canadian
 * (CASL, PIPEDA) wording in the starter legal pages. Slugs, keys, values, links and image paths are never touched.
 */
return new class extends Migration
{
    /** table => [plain/HTML text columns, JSON columns] */
    private const TEXT = [
        'pages' => [['title', 'h1', 'seo_title', 'meta_description', 'body'], ['content', 'sections']],
        'faqs' => [['question', 'answer'], []],
        'testimonials' => [['quote', 'trip'], []],
        'blog_posts' => [['title', 'excerpt', 'body', 'label', 'image_alt', 'seo_title', 'meta_description'], []],
        'packages' => [['title', 'summary', 'description', 'seo_title', 'meta_description'], ['highlights', 'itinerary', 'inclusions', 'exclusions', 'faqs']],
        'tour_types' => [['summary', 'h1', 'body', 'image_alt', 'seo_title', 'meta_description', 'style_note'], []],
        'destinations' => [['summary', 'h1', 'body', 'image_alt', 'seo_title', 'meta_description'], []],
        'quiz_steps' => [['question'], []],
        'quiz_options' => [['label'], []],
        'menu_items' => [['label', 'description', 'image_alt'], []],
    ];

    public function up(): void
    {
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
                            if ($new !== $row->$col) {
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

        // Site settings (never secrets, CRM credentials, contact details or files).
        foreach (DB::table('settings')->get() as $s) {
            if (is_string($s->value) && ! preg_match('/token|secret|key|ghl|recaptcha|logo|favicon|image|gtm|email|phone|sheets|_id$/i', $s->key)) {
                $new = UsSpelling::convert($s->value);
                if ($new !== $s->value) {
                    DB::table('settings')->where('key', $s->key)->update(['value' => $new]);
                }
            }
        }
        $this->setting('tagline', 'Self drive, chauffeur and coach tours of the Netherlands', 'Self drive, chauffeur and coach tours of the Netherlands for US and Canadian travelers');
        $this->setting('default_meta_description', 'Plan your Netherlands trip with local experts. Self drive, chauffeur driven and coach tours, tailored for you. Get a free quote in minutes.', 'Plan your Netherlands trip with local experts. Self drive, chauffeur driven and coach tours for US and Canadian travelers, tailored for you. Get a free quote in minutes.');

        $this->faqs();
        $this->legal();
    }

    private function setting(string $key, string $old, string $new): void
    {
        DB::table('settings')->where('key', $key)->where('value', $old)->update(['value' => $new]);
    }

    /** Entry, driving, airport, currency and insurance answers for US and Canadian visitors. A FAQ the team already reworded keeps its answer. */
    private function faqs(): void
    {
        $order = 0;
        foreach (FaqCopy::all() as [$q, $a, $replaces]) {
            $order++;
            $row = DB::table('faqs')->where('question', $q)->first() ?? ($replaces ? DB::table('faqs')->where('question', $replaces)->first() : null);
            if ($row) {
                $update = ['sort_order' => $order];
                if ($replaces && $row->question === $replaces) {
                    $update['question'] = $q;
                    $update['answer'] = $a;
                }
                DB::table('faqs')->where('id', $row->id)->update($update);
            } else {
                DB::table('faqs')->insert(['question' => $q, 'answer' => $a, 'sort_order' => $order, 'created_at' => now(), 'updated_at' => now()]);
            }
        }
        // Any FAQ the team added themselves stays, after the defaults.
        $n = $order;
        foreach (DB::table('faqs')->whereNotIn('question', array_column(FaqCopy::all(), 0))->orderBy('sort_order')->get() as $extra) {
            DB::table('faqs')->where('id', $extra->id)->update(['sort_order' => ++$n]);
        }
    }

    /** Canadian (CASL, PIPEDA) and US-state wording in the starter terms and privacy pages. Only added once. */
    private function legal(): void
    {
        $this->editHtml('terms', function (string $h): string {
            $h = preg_replace('/\benquiries\b/i', 'inquiries', $h);
            $h = preg_replace('/\benquiry\b/i', 'inquiry', $h);
            if (! str_contains($h, 'CASL')) {
                $h = str_replace('Consent is not a condition of purchase.', "Consent is not a condition of purchase. If you are in Canada, you give express consent under Canada's anti-spam law (CASL) and can withdraw it at any time.", $h);
            }
            if (! str_contains($h, 'quoted in euros')) {
                $h = str_replace('Quotes are valid for 14 days unless stated otherwise.', 'Quotes are valid for 14 days unless stated otherwise. Prices are quoted in euros (EUR); exchange-rate differences and any foreign transaction fees charged by your card issuer are your responsibility.', $h);
            }

            return $h;
        });
        $this->editHtml('privacy', function (string $h): string {
            $h = preg_replace('/\benquiries\b/i', 'inquiries', $h);
            $h = preg_replace('/\benquiry\b/i', 'inquiry', $h);
            if (! str_contains($h, 'PIPEDA')) {
                $h .= '<h2>8. Visitors from the US and Canada</h2><p>We serve travelers from the United States and Canada. If you live in Canada you may have rights under the Personal Information Protection and Electronic Documents Act (PIPEDA) or provincial privacy laws, and if you live in a US state with a consumer privacy law, such as California, you may have additional rights to know, correct and delete your information. Your information may be stored and processed in the United States and the European Union. To use any of these rights, email info@gonetherlandtours.com.</p>';
            }

            return $h;
        });
    }

    private function editHtml(string $slug, callable $edit): void
    {
        $page = DB::table('pages')->where('slug', $slug)->first(['id', 'sections']);
        $sections = $page ? json_decode((string) $page->sections, true) : null;
        if (! is_array($sections)) {
            return;
        }
        foreach ($sections as &$s) {
            if (($s['type'] ?? '') === 'rich_text' && is_string($s['data']['html'] ?? null)) {
                $s['data']['html'] = $edit($s['data']['html']);
            }
        }
        unset($s);
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
