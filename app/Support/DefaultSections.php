<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Builds the starting sections (and menus) that reproduce the site exactly as it was when its content lived in Blade files.
 * Used by the "make pages dynamic" migration (keeps text admins already edited) and by PagesSeeder on fresh installs.
 * Uses the DB facade, not models, so it keeps working however the models change later.
 */
class DefaultSections
{
    /** Pages that ship with the site: they can be edited but not deleted or renamed. */
    public const SYSTEM = ['home', 'about', 'contact', 'terms', 'privacy', 'faq', 'testimonials'];

    private static function s(string $type, array $data, bool $visible = true): array
    {
        return ['id' => Str::lower(Str::random(8)), 'type' => $type, 'visible' => $visible, 'data' => $data + Sections::defaults($type)];
    }

    private static function rows(array $rows, array $keys): array
    {
        return array_map(fn ($r) => array_combine($keys, array_pad(array_values((array) $r), count($keys), '')), $rows);
    }

    /** Fill (or create) the sections of every system page that has none yet. Safe to run repeatedly. */
    public static function fill(): void
    {
        $now = now();
        foreach ([
            'faq' => ['FAQ', 'Frequently asked questions', 'Netherlands Tour FAQs | GoNetherlandTours', 'Answers to common questions about our Netherlands self drive, chauffeur driven and coach tours: when to go, what is included, driving and entry requirements.'],
            'testimonials' => ['Testimonials', 'What our travellers say', 'Traveller Reviews | GoNetherlandTours', 'Read what travellers say about their Netherlands tours with GoNetherlandTours.'],
        ] as $slug => [$title, $h1, $seo, $meta]) {
            if (! DB::table('pages')->where('slug', $slug)->exists()) {
                DB::table('pages')->insert(['slug' => $slug, 'title' => $title, 'h1' => $h1, 'seo_title' => $seo, 'meta_description' => $meta, 'created_at' => $now, 'updated_at' => $now]);
            }
        }

        foreach (DB::table('pages')->get() as $page) {
            $update = ['is_system' => in_array($page->slug, self::SYSTEM, true)];
            if (blank($page->sections)) {
                $content = json_decode((string) $page->content, true) ?: [];
                $sections = match ($page->slug) {
                    'home' => self::home($page->h1, $content),
                    'about' => self::about(),
                    'contact' => self::contact((string) $page->body),
                    'terms', 'privacy' => self::legal((string) $page->body),
                    'faq' => self::faq(),
                    'testimonials' => self::testimonials(),
                    default => self::legal((string) $page->body), // any other existing page keeps its text
                };
                $update['sections'] = json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
            DB::table('pages')->where('id', $page->id)->update($update);
        }
    }

    public static function home(?string $h1, array $c): array
    {
        $g = $c['gallery'] ?? null;
        $gallery = is_array($g) && ! empty($g['items']) ? $g : [
            'eyebrow' => 'Visual journey', 'title' => 'A glimpse of the *real Netherlands*',
            'items' => [
                ['caption' => 'First-time visitors', 'image' => 'images/first_time_visitors.webp', 'alt' => 'Amsterdam canal houses and a canal boat on a bright morning'],
                ['caption' => 'Couples', 'image' => 'images/couples.webp', 'alt' => 'A couple dining at a candle-lit canal-side restaurant at dusk'],
                ['caption' => 'Families', 'image' => 'images/families.webp', 'alt' => 'A family cycling past a windmill and green fields'],
                ['caption' => 'Culture lovers', 'image' => 'images/culture_lovers.webp', 'alt' => 'A market vendor stacking wheels of Gouda cheese'],
                ['caption' => 'Off the beaten path', 'image' => 'images/off_the_beaten_path.webp', 'alt' => 'A quiet, empty Dutch harbour village with colourful boats in morning mist'],
                ['caption' => 'Special occasions', 'image' => 'images/special_occasions.webp', 'alt' => 'A table set with champagne on a terrace overlooking a canal at sunset'],
            ],
        ];
        $galleryRows = array_map(fn ($i) => ['caption' => $i['caption'] ?? '', 'image' => $i['image'] ?? '', 'image_alt' => $i['alt'] ?? ($i['image_alt'] ?? '')], $gallery['items']);

        $why = $c['why'] ?? [['title' => 'Experience Authentic Dutch Culture', 'text' => 'Explore traditional windmills, colourful tulip fields, cheese markets and local traditions.']];
        $steps = $c['steps'] ?? [];
        $trav = [
            ['First-time visitors', 'The essential Netherlands, paced so you see more and rush less.', 'images/first_time_visitors.webp', 'Amsterdam canal houses and a canal boat on a bright morning'],
            ['Couples', 'Quiet canal towns, long dinners and time to simply enjoy each other.', 'images/couples.webp', 'A couple dining at a candle-lit canal-side restaurant at dusk'],
            ['Families', 'Windmills, cheese markets and hands-on traditions the whole group will remember.', 'images/families.webp', 'A family cycling past a windmill and green fields'],
            ['Culture lovers', 'Local traditions, living crafts and the stories behind them.', 'images/culture_lovers.webp', 'A market vendor stacking wheels of Gouda cheese'],
            ['Off the beaten path', 'Lesser-known villages and local secrets away from the crowds.', 'images/off_the_beaten_path.webp', 'A quiet, empty Dutch harbour village with colourful boats in morning mist'],
            ['Special occasions', 'Anniversaries and milestones, thoughtfully planned.', 'images/special_occasions.webp', 'A table set with champagne on a terrace overlooking a canal at sunset'],
        ];

        return [
            self::s('hero_video_quiz', [
                'heading' => $h1 ?: 'Discover the Authentic Netherlands',
                'subheading' => $c['hero_sub'] ?? '',
                'badges' => array_map(fn ($b) => ['text' => $b], $c['badges'] ?? []),
                'poster_alt' => '',
            ]),
            self::s('ticker', ['items' => array_map(fn ($t) => ['text' => $t], [
                'Authentic Dutch Culture', 'Small Group Experiences', 'Hidden Gems & Local Secrets', 'Expertly Crafted Itineraries',
                'Windmills & Tulip Fields', 'Cheese Markets & Local Traditions', 'Flexible Itineraries', 'Free, No-Obligation Quote',
            ])]),
            self::s('split_list', [
                'image1_alt' => 'A quiet Dutch canal town at dawn with gabled brick houses reflected in still water',
                'image2_alt' => 'A traditional windmill beside rows of tulips at golden hour',
                'items' => array_map(fn ($w) => ['title' => $w['title'] ?? '', 'text' => $w['text'] ?? ''], $why),
            ]),
            self::s('packages_tabs', ['heading' => $c['packages_heading'] ?? 'Signature Netherlands Journeys']),
            self::s('feature_band', [
                'image_alt' => 'Dutch countryside at sunrise with windmills along a river',
                'bullets' => array_map(fn ($t) => ['text' => $t], ['Authentic Dutch culture, not a checklist', 'Small groups with flexible itineraries', 'Hidden gems beyond the tourist hotspots', 'Expertly crafted routes and attractions']),
            ]),
            self::s('tour_types', ['heading' => $c['tours_heading'] ?? 'Choose How You Want to Explore']),
            self::s('video_feature', ['poster_alt' => 'Travellers walking along a canal-side street lined with historic houses']),
            self::s('image_strip', ['items' => array_map(fn ($t) => ['title' => $t[0], 'text' => $t[1], 'image' => $t[2], 'image_alt' => $t[3]], $trav)]),
            self::s('steps', ['heading' => $c['how_heading'] ?? 'How It Works', 'items' => array_map(fn ($x) => ['title' => $x['title'] ?? '', 'text' => $x['text'] ?? ''], $steps)]),
            self::s('testimonials', ['variant' => 'dark', 'heading' => $c['testimonials_heading'] ?? 'What Travellers Say']),
            self::s('gallery', ['eyebrow' => $gallery['eyebrow'] ?? 'Visual journey', 'heading' => $gallery['title'] ?? 'A glimpse of the *real Netherlands*', 'items' => $galleryRows], (bool) ($c['show_gallery'] ?? true)),
            self::s('text_cta', [
                'heading' => 'Plan with *peace of mind*',
                'text' => 'We design thoughtful routes with carefully selected attractions, in small groups, with room to explore. Real people, clear quotes, no pressure. [CONFIRM: company story]',
                'buttons' => [['label' => 'About us', 'url' => '/about'], ['label' => 'Contact us', 'url' => '/contact']],
            ]),
            self::s('faq', ['heading' => $c['faq_heading'] ?? 'Frequently Asked Questions']),
            self::s('blog_latest', []),
            self::s('cta_banner', [
                'image_alt' => 'Tulip fields and a distant windmill at sunset',
                'heading' => $c['cta_heading'] ?? 'Ready to Discover the Real Netherlands?',
                'text' => $c['cta_text'] ?? '',
            ]),
        ];
    }

    public static function about(): array
    {
        return [
            self::s('hero_seasons', [
                'image_alt' => 'Dutch countryside with a windmill and tulip fields at sunrise',
                'eyebrow' => 'About GoNetherlandTours',
                'heading' => 'About Us',
                'text' => 'At GoNetherlandTours, we plan Netherlands holidays for American travellers who want the real thing: tulip fields in bloom, windmills along the polders, canal-side towns and world-class museums. Our travel specialists handle the planning, the drivers and the logistics, so you can enjoy every day.',
                'seasons' => self::rows([['spring', 'Spring', 'Tulip season'], ['summer', 'Summer', 'Long days, coast and canals'], ['autumn', 'Autumn', 'Golden light, quiet cities'], ['winter', 'Winter', 'Christmas markets, cosy canals']], ['icon', 'name', 'note']),
                'badges' => self::rows([['shield', 'Local Netherlands specialists'], ['map', 'Tailor-made itineraries'], ['car', 'Self drive, chauffeur and coach options'], ['star', '[CONFIRM: traveller reviews badge]']], ['icon', 'text']),
            ]),
            self::s('feature_cards', ['items' => self::rows([
                ['map', 'Tailor-Made Trips', 'Every itinerary is built around your dates, pace and interests, not a one-size-fits-all template.'],
                ['car', 'Your Way to Travel', 'Choose self drive, chauffeur driven or coach touring, whichever suits you best.'],
                ['shield', 'Built for US Travellers', 'Prices in EUR, plain-English planning and help with the things first-time visitors ask about, like driving rules and entry requirements.'],
                ['phone', 'Support When You Need It', 'A real travel specialist plans your trip and is there to answer questions before you go. [CONFIRM: support hours / in-trip support]'],
            ], ['icon', 'title', 'text'])]),
            self::s('split_columns', [
                'left_image_alt' => 'A quiet Dutch canal town at dawn with gabled brick houses reflected in still water',
                'left_heading' => 'Netherlands Journeys *Designed for Discovery*',
                'left_text' => 'At GoNetherlandTours, we believe a great trip is more than a list of sights. The Netherlands is compact, beautiful and easy to love: you can wake up in a canal house in Amsterdam, spend the afternoon among the windmills of Kinderdijk and finish the day in a Delft café. We design routes that make the most of that variety without rushing you. We plan self drive, chauffeur driven and coach tours for American travellers, with clear pricing and a specialist who knows the country.',
                'right_image_alt' => 'A traditional windmill beside rows of tulips at golden hour',
                'right_heading' => 'Discover the Netherlands Your Way',
                'right_text' => 'From the tulip fields of the Bollenstreek to the Dutch Golden Age cities, we shape trips around what matters to you. Whether it is your first visit or a return trip to see the places you missed, we will build something that fits.',
                'right_bullets' => array_map(fn ($t) => ['text' => $t], [
                    'Tailor-made Netherlands itineraries', 'Self drive, chauffeur driven and coach tours',
                    'Handpicked hotels, from canal-house boutiques to countryside stays [CONFIRM: accommodation standard]',
                    'Timed-entry planning for top sights such as Keukenhof and the Rijksmuseum',
                    'Pre-trip guidance on driving, travel documents and packing',
                    'Support from a Netherlands travel specialist [CONFIRM: hours]',
                ]),
            ]),
            self::s('why_band', [
                'heading' => 'Why Tour With GoNetherlandTours?',
                'image_alt' => 'Dutch countryside at sunrise with windmills along a river',
                'items' => self::rows([
                    ['The Best of the Netherlands', 'From Amsterdam and the Dutch coast to Utrecht, Delft, Haarlem, Rotterdam and the villages of Giethoorn. We balance famous highlights with quieter places.'],
                    ['Chosen for the Season', 'Tulip season, summer by the sea, autumn colour or Christmas markets. We plan around what the Netherlands looks like when you are there. [CONFIRM: tulip season dates]'],
                    ['Three Ways to Travel', 'Drive yourself with a route and bookings in place, relax with a private chauffeur, or join a coach tour. Same destinations, your choice of pace.'],
                    ['Hassle-Free Planning', 'We help organise the details, including accommodation, transfers, route planning and timed entries, so you do not have to piece it together yourself.'],
                    ['Local Knowledge', 'Dutch trips come with practical details: when to book the Anne Frank House, how cycle lanes work for drivers, and where to go when the tulip fields are crowded.'],
                    ['Clear, Honest Quotes', 'Tell us what you want and we will send a tailored quote with no obligation. Prices are in EUR. [CONFIRM: what is included in a standard quote]'],
                ], ['title', 'text']),
            ]),
            self::s('team', ['items' => array_map(fn ($n) => ['name' => "[CONFIRM: team member $n name]", 'role' => '[CONFIRM: role]', 'bio' => '[CONFIRM: short bio and why they love the Netherlands. Sample text only.]', 'image' => '', 'image_alt' => ''], [1, 2, 3])]),
            self::s('testimonials', ['variant' => 'light', 'eyebrow' => '', 'heading' => 'What Our Travellers Say']),
            self::s('cta_banner', [
                'image_alt' => 'Tulip fields and a distant windmill at sunset',
                'heading' => 'Planning your first Dutch trip?',
                'text' => 'Answer a few quick questions and we will help you work out the right season, route and style of travel.',
                'button_label' => 'Get a free quote',
                'phone_label' => 'Talk to a Netherlands travel specialist:',
            ]),
        ];
    }

    public static function contact(string $body): array
    {
        return [self::s('page_header', ['heading' => '']), self::s('contact_details', ['intro' => $body])];
    }

    public static function legal(string $body): array
    {
        return [self::s('page_header', ['heading' => '']), self::s('rich_text', ['html' => $body, 'show_version' => true])];
    }

    public static function faq(): array
    {
        return [
            self::s('page_header', ['align' => 'center', 'tone' => 'blue', 'subtitle' => 'Quick answers about planning a Netherlands trip with us. Cannot find yours? Ask and we will reply.']),
            self::s('faq', ['eyebrow' => '', 'heading' => '', 'help_heading' => 'Still have a question?', 'help_text' => 'Tell us about your trip and a travel specialist will get back to you.']),
        ];
    }

    public static function testimonials(): array
    {
        return [
            self::s('page_header', ['align' => 'center', 'tone' => 'blue', 'subtitle' => 'Real words from people who have travelled the Netherlands with us.']),
            self::s('testimonials', ['variant' => 'light', 'eyebrow' => '', 'heading' => '', 'button_label' => 'Plan my Netherlands trip']),
        ];
    }

    /** Header and footer menus as they were hard-coded. Only inserted when the menu table is empty. */
    public static function menus(): void
    {
        if (DB::table('menu_items')->exists()) {
            return;
        }
        $now = now();
        $add = function (array $row) use ($now) {
            return DB::table('menu_items')->insertGetId($row + ['kind' => 'link', 'description' => null, 'image' => null, 'image_alt' => null, 'parent_id' => null, 'is_active' => true, 'new_tab' => false, 'created_at' => $now, 'updated_at' => $now]);
        };
        $n = 0;
        $add(['menu' => 'header', 'label' => 'Tours', 'kind' => 'tours', 'url' => null, 'sort_order' => ++$n]);
        $add(['menu' => 'header', 'label' => 'Packages', 'url' => '/packages', 'sort_order' => ++$n]);
        $about = $add(['menu' => 'header', 'label' => 'About Us', 'kind' => 'cards', 'url' => '/about', 'sort_order' => ++$n]);
        $add(['menu' => 'header', 'label' => 'Blog', 'url' => '/blog', 'sort_order' => ++$n]);
        $add(['menu' => 'header', 'label' => 'Contact', 'url' => '/contact', 'sort_order' => ++$n]);
        foreach ([
            ['About Us', 'Who we are and how we plan your trip', '/about', 'images/Intro_image_split.webp', 'A traditional Dutch canal house and windmill'],
            ['Contact', 'Talk to a travel specialist', '/contact', 'images/Guide_1.webp', 'A travel specialist helping plan a Netherlands trip'],
            ['Testimonials', 'What travellers say about us', '/testimonials', 'images/couples.webp', 'A happy couple travelling in the Netherlands'],
            ['FAQs', 'Answers to common questions', '/faq', 'images/feature_band.webp', 'Tulip fields stretching to the horizon'],
        ] as $i => [$label, $desc, $url, $img, $alt]) {
            $add(['menu' => 'header', 'parent_id' => $about, 'label' => $label, 'description' => $desc, 'url' => $url, 'image' => $img, 'image_alt' => $alt, 'sort_order' => $i + 1]);
        }
        foreach ([['About us', '/about'], ['Blog', '/blog'], ['FAQ', '/faq'], ['Testimonials', '/testimonials'], ['Contact', '/contact'], ['Terms and Conditions', '/terms-and-conditions'], ['Privacy Policy', '/privacy-policy']] as $i => [$label, $url]) {
            $add(['menu' => 'footer', 'label' => $label, 'url' => $url, 'sort_order' => $i + 1]);
        }
    }
}
