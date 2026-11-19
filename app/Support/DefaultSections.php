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
            'testimonials' => ['Testimonials', 'What our travelers say', 'Traveler Reviews | GoNetherlandTours', 'Read what travelers say about their Netherlands tours with GoNetherlandTours.'],
        ] as $slug => [$title, $h1, $seo, $meta]) {
            if (! DB::table('pages')->where('slug', $slug)->exists()) {
                DB::table('pages')->insert(['slug' => $slug, 'title' => $title, 'h1' => $h1, 'seo_title' => $seo, 'meta_description' => $meta, 'created_at' => $now, 'updated_at' => $now]);
            }
        }

        foreach (DB::table('pages')->get() as $page) {
            $update = ['is_system' => in_array($page->slug, self::SYSTEM, true) || ($page->is_system && (str_starts_with($page->slug, 'tour-') || str_starts_with($page->slug, 'dest-')))];
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
                ['caption' => 'Off the beaten path', 'image' => 'images/off_the_beaten_path.webp', 'alt' => 'A quiet, empty Dutch harbour village with colorful boats in morning mist'],
                ['caption' => 'Special occasions', 'image' => 'images/special_occasions.webp', 'alt' => 'A table set with champagne on a terrace overlooking a canal at sunset'],
            ],
        ];
        $galleryRows = array_map(fn ($i) => ['caption' => $i['caption'] ?? '', 'image' => $i['image'] ?? '', 'image_alt' => $i['alt'] ?? ($i['image_alt'] ?? '')], $gallery['items']);

        $why = $c['why'] ?? [['title' => 'Experience Authentic Dutch Culture', 'text' => 'Explore traditional windmills, colorful tulip fields, cheese markets and local traditions.']];
        $steps = $c['steps'] ?? [];
        $trav = [
            ['First-time visitors', 'The essential Netherlands, paced so you see more and rush less.', 'images/first_time_visitors.webp', 'Amsterdam canal houses and a canal boat on a bright morning'],
            ['Couples', 'Quiet canal towns, long dinners and time to simply enjoy each other.', 'images/couples.webp', 'A couple dining at a candle-lit canal-side restaurant at dusk'],
            ['Families', 'Windmills, cheese markets and hands-on traditions the whole group will remember.', 'images/families.webp', 'A family cycling past a windmill and green fields'],
            ['Culture lovers', 'Local traditions, living crafts and the stories behind them.', 'images/culture_lovers.webp', 'A market vendor stacking wheels of Gouda cheese'],
            ['Off the beaten path', 'Lesser-known villages and local secrets away from the crowds.', 'images/off_the_beaten_path.webp', 'A quiet, empty Dutch harbour village with colorful boats in morning mist'],
            ['Special occasions', 'Anniversaries and milestones, thoughtfully planned.', 'images/special_occasions.webp', 'A table set with champagne on a terrace overlooking a canal at sunset'],
        ];

        return [
            self::s('hero_video_quiz', [
                'heading' => $h1 ?: 'Discover the Authentic Netherlands',
                'subheading' => $c['hero_sub'] ?? '',
                'badges' => array_map(fn ($b) => ['text' => $b], $c['badges'] ?? []),
                'poster_alt' => '',
            ]),
            self::valueStrip(),
            self::s('split_list', [
                'image1_alt' => 'A quiet Dutch canal town at dawn with gabled brick houses reflected in still water',
                'image2_alt' => 'A traditional windmill beside rows of tulips at golden hour',
                'items' => array_map(fn ($w) => ['title' => $w['title'] ?? '', 'text' => $w['text'] ?? ''], $why),
            ]),
            self::s('tour_types', ['heading' => $c['tours_heading'] ?? 'Choose How You Want to Explore']),
            self::s('packages_tabs', ['heading' => $c['packages_heading'] ?? 'Signature Collection']),
            self::whyBand(),
            self::uspProof(),
            self::s('image_strip', ['items' => array_map(fn ($t) => ['title' => $t[0], 'text' => $t[1], 'image' => $t[2], 'image_alt' => $t[3]], $trav)]),
            self::s('steps', ['heading' => $c['how_heading'] ?? 'How It Works', 'items' => array_map(fn ($x) => ['title' => $x['title'] ?? '', 'text' => $x['text'] ?? ''], $steps)]),
            self::partners(),
            self::s('testimonials', ['variant' => 'dark', 'heading' => $c['testimonials_heading'] ?? 'What Travelers Say']),   // three review cards over a photo ("Spotlight" = one review at a time is still available)
            self::s('gallery', ['eyebrow' => $gallery['eyebrow'] ?? 'Visual journey', 'heading' => $gallery['title'] ?? 'A glimpse of the *real Netherlands*', 'items' => $galleryRows], (bool) ($c['show_gallery'] ?? true)),
            self::s('blog_latest', []),
            self::s('cta_banner', [
                'image_alt' => 'Tulip fields and a distant windmill at sunset',
                'heading' => $c['cta_heading'] ?? 'Ready to Discover the Real Netherlands?',
                'text' => $c['cta_text'] ?? '',
            ]),
        ];
    }

    /** Partner logos band after "How it works". White or transparent logos on the dark band; replace or remove them in Admin > Pages. */
    public static function partners(): array
    {
        return self::s('partners', ['items' => self::rows([
            ['images/partners/hen-it.png', 'HEN IT, Ireland leading hen company', ''],
            ['images/partners/go-asian-tours.png', 'Go Asian Tours', ''],
            ['images/partners/go-italia-trips.webp', 'Go Italia Trips', ''],
        ], ['image', 'image_alt', 'url'])]);
    }

    /** Four short selling points in a clean row right under the hero. */
    public static function valueStrip(): array
    {
        return self::s('value_strip', ['items' => self::rows([
            ['money', 'Clear, upfront quotes', 'Know what is included before you decide.'],
            ['tulip', 'Real Dutch culture', 'Local traditions, not a checklist.'],
            ['guide', 'Private or small group', 'Your own party, or a small guided group.'],
            ['phone', 'Real people to help', 'Help from first inquiry to return home.'],
        ], ['icon', 'title', 'text'])]);
    }

    /** Dark "why choose us" band: photo and four reason tiles (the same four selling points as the footer/USP text). */
    public static function whyBand(): array
    {
        return self::s('why_band', [
            'heading' => 'Why we are the *right choice* for you',
            'image_alt' => 'Dutch countryside at sunrise with windmills along a river',
            'items' => self::rows([
                ['tulip', 'Real Dutch culture', 'Cheese markets, windmills, tulip fields and living local traditions, not just a checklist of sights.'],
                ['guide', 'Private or small group', 'Self drive and chauffeur tours are just your own party. Coach tours are small guided groups.'],
                ['search', 'Hidden gems, local secrets', 'Quiet canal towns and lesser-known corners beyond the usual tourist hotspots.'],
                ['map', 'Itineraries built around you', 'Routes shaped by your dates, pace and interests, with hotels and driving distances planned for you.'],
            ], ['icon', 'title', 'text']),
        ]);
    }

    /** "Our promise in practice": each selling point with a placeholder proof to be replaced with a real, checkable example. */
    public static function uspProof(): array
    {
        return self::s('usp_proof', ['items' => self::rows([
            ['tulip', 'Authentic Dutch Culture', 'Real traditions and local places, not a checklist of sights.', 'Where your dates allow, we build in living traditions rather than a checklist of sights: Alkmaar\'s Friday-morning cheese market (April to September), Gouda\'s Thursday-morning market (April to August), the 19 windmills of Kinderdijk, or early-morning canal lanes in Giethoorn before the day trippers arrive. [CONFIRM: which of these are on your itineraries]', 'Explore our destinations', '/destinations'],
            ['guide', 'Small-Group & Private Tours', 'Coach tours run as small groups; self drive and chauffeur tours are just your own party.', 'Coach tours are limited to [CONFIRM: N] travelers, so your guide knows your name and can adjust the plan on the day. Self drive and chauffeur tours are private: just your own party, on your own timing.', 'Read our travel journal', '/blog'],
            ['search', 'Hidden Gems & Local Secrets', 'Lesser-known towns and corners beyond the usual hotspots.', 'Beyond the Amsterdam and tulip-field circuit, we plan time in quieter places such as [CONFIRM: three named places, for example Zierikzee, Veere and Dordrecht], where you can walk a historic town center without the crowds.', 'Guides and inspiration', '/blog'],
            ['map', 'Expertly Crafted Itineraries', 'Routes designed around your dates, pace and interests.', 'Every itinerary is built around your dates, pace and interests by [CONFIRM: who plans the trips and their experience], including hotel choices, driving distances and timed entry for busy sights. You get a clear, personal quote before you decide.', 'See sample itineraries', '/packages'],
        ], ['icon', 'title', 'text', 'proof', 'link_label', 'link'])]);
    }

    public static function about(): array
    {
        // Layout follows the reference About page: the home banner (photo + quiz), a slim trust strip, welcome text + photo, about text + photo,
        // dark "why us" tiles, articles, reviews, three numbered steps and a closing banner.
        return [
            self::s('hero_video_quiz', [   // same banner as the home page (heading, badges, quiz) but a photo only, no background video
                'eyebrow' => 'Explore tours today',
                'heading' => 'About *GoNetherlandTours*',
                'subheading' => 'We plan unforgettable Netherlands journeys for American travelers, with tulip fields, windmills and canal towns, expert planning and personal service.',
                'secondary_label' => 'Explore packages',
                'secondary_url' => '/packages',
                'badges' => self::rows([['Local Netherlands specialists'], ['Tailor-made itineraries'], ['Self drive, chauffeur and coach']], ['text']),
                'poster' => 'images/hero-about.webp',
                'show_video' => false,
            ]),
            self::s('feature_cards', ['style' => 'strip', 'items' => self::rows([
                ['shield', 'Tailor-Made Trips', 'Every itinerary is built around your dates, pace and interests.'],
                ['guide', 'Local Specialists', 'Real Netherlands travel specialists plan your trip and answer your questions.'],
                ['money', 'Clear, Honest Quotes', 'Prices in EUR and a tailored quote with no obligation.'],
            ], ['icon', 'title', 'text'])]),
            self::s('text_image', [
                'image_side' => 'right', 'background' => 'sand', 'image' => 'images/first_time_visitors.webp',
                'image_alt' => 'Amsterdam canal houses and a canal boat on a bright morning',
                'heading' => 'Welcome to the Best Netherlands Tours',
                'text' => "GoNetherlandTours plans Netherlands holidays for American travelers who want the real thing: tulip fields in bloom, windmills along the polders, canal-side towns and world-class museums.\n\nOur travel specialists handle the planning, the drivers and the logistics, so you can enjoy every day. Whether it is your first visit or a return trip to see the places you missed, we build something that fits.",
            ]),
            self::s('text_image', [
                'image_side' => 'left', 'background' => 'white', 'image' => 'images/culture_lovers.webp',
                'image_alt' => 'A market vendor stacking wheels of Gouda cheese',
                'heading' => 'About GoNetherlandTours',
                'quote' => '"We believe the best journeys leave lasting memories."',
                'text' => "The Netherlands is compact, beautiful and easy to love: you can wake up in a canal house in Amsterdam, spend the afternoon among the windmills of Kinderdijk and finish the day in a Delft cafe. We design routes that make the most of that variety without rushing you.\n\nWe plan self drive, chauffeur driven and coach tours with clear pricing and a specialist who knows the country. Each trip is tailor-made to your travel goals.",
                'button_label' => 'Get Instant Quote',
            ]),
            self::whyBand(),   // the same "Why choose us" section as the home page
            self::s('blog_latest', ['eyebrow' => '', 'heading' => 'Our Articles', 'intro' => 'Guides, insights and stories to inspire your next Netherlands trip.', 'count' => 3, 'button_label' => '']),
            self::s('team', ['items' => array_map(fn ($n) => ['name' => "[CONFIRM: team member $n name]", 'role' => '[CONFIRM: role]', 'bio' => '[CONFIRM: short bio and why they love the Netherlands. Sample text only.]', 'image' => '', 'image_alt' => ''], [1, 2, 3])]),
            self::s('testimonials', ['variant' => 'dark', 'eyebrow' => '', 'heading' => 'What Our Travelers Say']),
            self::s('steps', ['style' => 'tiles', 'heading' => '3 Easy Steps to Book Your Netherlands Tour', 'intro' => 'Follow three easy steps to choose your tour, shape your trip and get ready for an unforgettable adventure.', 'button_label' => 'Start Your Netherlands Dream Trip', 'items' => self::rows([
                ['Get Your Free Quote', 'Tell us your travel plans and preferences.'],
                ['Review Your Itinerary', 'Receive a personalized itinerary and pricing, tailored just for you.'],
                ['Travel With Confidence', 'We handle hotels, transport and experiences, so you simply arrive and enjoy the Netherlands.'],
            ], ['title', 'text'])]),
            self::s('cta_banner', [
                'image_alt' => 'Tulip fields and a distant windmill at sunset',
                'heading' => 'Ready to Start?',
                'text' => 'Experience the tulip fields, windmills and canal towns of the Netherlands on expertly planned tours.',
                'button_label' => 'Get Free Quote',
                'phone_label' => 'Talk to a Netherlands travel specialist:',
            ]),
        ];
    }

    /** "What happens next" steps shown under the contact form (also appended to an existing default Contact page by a migration). */
    public static function contactSteps(): array
    {
        return self::s('steps', ['style' => 'tiles', 'heading' => 'What happens next', 'intro' => 'Three simple steps from your message to your trip.', 'items' => self::rows([
            ['Tell us your plans', 'Send your dates, group size and the kind of trip you have in mind.'],
            ['Get your tailored quote', 'A Netherlands travel specialist builds your itinerary and sends a quote with no obligation.'],
            ['Travel with confidence', 'We help with bookings and planning, and you get guidance before you go.'],
        ], ['title', 'text'])]);
    }

    public static function contact(string $body): array
    {
        return [self::s('page_header', ['heading' => '']), self::s('contact_details', ['intro' => $body]), self::contactSteps()];
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
            self::s('page_header', ['align' => 'center', 'tone' => 'blue', 'subtitle' => 'Real words from people who have traveled the Netherlands with us.']),
            self::s('testimonials', ['variant' => 'light', 'eyebrow' => '', 'heading' => '', 'button_label' => 'Plan my Netherlands trip']),
        ];
    }

    /** Header and footer menus as they were hard-coded. Only inserted when the menu table is empty. */
    public static function menus(): void
    {
        if (DB::table('menu_items')->exists()) {
            return;
        }
        self::resetHeaderMenu();
        $now = now();
        foreach ([['About us', '/about'], ['Meet the team', '/meet-the-team'], ['Blog', '/blog'], ['FAQ', '/faq'], ['Testimonials', '/testimonials'], ['Contact', '/contact'], ['Terms and Conditions', '/terms-and-conditions'], ['Privacy Policy', '/privacy-policy']] as $i => [$label, $url]) {
            DB::table('menu_items')->insert(['menu' => 'footer', 'label' => $label, 'url' => $url, 'sort_order' => $i + 1, 'kind' => 'link', 'description' => null, 'image' => null, 'image_alt' => null, 'parent_id' => null, 'is_active' => true, 'new_tab' => false, 'created_at' => $now, 'updated_at' => $now]);
        }
    }

    /**
     * The main menu: Home, Tour Types (an automatic mega menu with a photo card per tour type), Destinations, Packages and an About Us dropdown of image
     * cards. Replaces ALL header menu rows, so callers must check that the admin has not customised the menu first.
     */
    public static function resetHeaderMenu(): void
    {
        DB::table('menu_items')->where('menu', 'header')->delete();
        $now = now();
        $add = function (array $row) use ($now) {
            return DB::table('menu_items')->insertGetId($row + ['menu' => 'header', 'kind' => 'link', 'description' => null, 'image' => null, 'image_alt' => null, 'parent_id' => null, 'is_active' => true, 'new_tab' => false, 'created_at' => $now, 'updated_at' => $now]);
        };
        $n = 0;
        $add(['label' => 'Home', 'url' => '/', 'sort_order' => ++$n]);
        $add(['label' => 'Tour Types', 'kind' => 'tours', 'url' => null, 'sort_order' => ++$n]);   // the automatic mega menu of tour types
        $add(['label' => 'Destinations', 'url' => '/destinations', 'sort_order' => ++$n]);
        $add(['label' => 'Packages', 'url' => '/packages', 'sort_order' => ++$n]);
        $about = $add(['label' => 'About Us', 'kind' => 'cards', 'url' => '/about', 'sort_order' => ++$n]);
        foreach ([
            ['About Us', 'Who we are and how we plan your trip', '/about', 'images/Intro_image_split.webp', 'A traditional Dutch canal house and windmill'],
            ['Meet the Team', 'The people planning your trip', '/meet-the-team', 'images/Guide_1.webp', 'A travel specialist helping plan a Netherlands trip'],
            ['Testimonials', 'What travelers say about us', '/testimonials', 'images/couples.webp', 'A happy couple traveling in the Netherlands'],
            ['FAQs', 'Answers to common questions', '/faq', 'images/feature_band.webp', 'Tulip fields stretching to the horizon'],
            ['Blog', 'Travel guides and inspiration', '/blog', 'images/Guide_2.webp', 'A Dutch canal-side street with historic gabled houses'],
            ['Contact', 'Talk to a travel specialist', '/contact', 'images/Guide_1.webp', 'A travel specialist helping plan a Netherlands trip'],
        ] as $i => [$label, $desc, $url, $img, $alt]) {
            $add(['parent_id' => $about, 'label' => $label, 'description' => $desc, 'url' => $url, 'image' => $img, 'image_alt' => $alt, 'sort_order' => $i + 1]);
        }
    }

    /** The Meet the Team page: title bar, team cards, closing call to action. */
    public static function teamPage(): array
    {
        return [
            self::s('page_header', ['heading' => 'Meet the Team', 'subtitle' => 'The people who plan your trip and look after you along the way.']),
            self::s('team', ['items' => array_map(fn ($n) => ['name' => "[CONFIRM: team member $n name]", 'role' => '[CONFIRM: role]', 'bio' => '[CONFIRM: short bio and why they love the Netherlands. Sample text only.]', 'image' => '', 'image_alt' => ''], [1, 2, 3])]),
            self::s('cta_banner', ['image_alt' => 'Tulip fields and a distant windmill at sunset', 'heading' => 'Ready to Discover the Real Netherlands?', 'text' => 'Tell us what you have in mind and a travel specialist will reply with a clear, no-obligation quote.']),
        ];
    }
}
