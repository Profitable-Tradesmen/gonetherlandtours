<?php

namespace App\Support;

use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * The page builder's section catalog. Each section type = a Blade view (resources/views/sections/{type}.blade.php)
 * + a field schema that drives the admin form, validation/sanitising and defaults.
 *
 * Field types: text, textarea, rich, link, image, media, select, checkbox, number, repeater.
 *  - media (top-level only): one background that can be a photo, an animated GIF or a short video (mp4/webm). Saves the path in "name".
 *  - text/textarea headings accept *word* for an italic accent word.
 *  - image fields save the path in "name" and the alt text in "name_alt".
 *  - repeater: "fields" = sub-fields (no nested repeaters), "max" = item limit, "label" = what one item is called.
 * To add a section type: add it here, add its view, done (the admin picks it up automatically).
 */
class Sections
{
    public const ICONS = ['map', 'car', 'shield', 'phone', 'star', 'heart', 'check', 'coach', 'guide', 'mail', 'lock', 'search', 'money', 'city', 'tulip', 'spring', 'summer', 'autumn', 'winter'];

    private static function t(string $name, string $label, string $default = '', string $help = ''): array
    {
        return ['name' => $name, 'label' => $label, 'type' => 'text', 'default' => $default, 'help' => $help];
    }

    private static function ta(string $name, string $label, string $default = '', string $help = ''): array
    {
        return ['name' => $name, 'label' => $label, 'type' => 'textarea', 'default' => $default, 'help' => $help];
    }

    private static function link(string $name, string $label, string $default = '', string $help = 'A page path such as /about, a full https:// address, #section, tel: or mailto:. Leave blank to open the quote pop-up.'): array
    {
        return ['name' => $name, 'label' => $label, 'type' => 'link', 'default' => $default, 'help' => $help];
    }

    private static function img(string $name, string $label, string $default = ''): array
    {
        return ['name' => $name, 'label' => $label, 'type' => 'image', 'default' => $default];
    }

    private static function rep(string $name, string $label, string $item, array $fields, int $max = 12, array $default = []): array
    {
        return ['name' => $name, 'label' => $label, 'type' => 'repeater', 'item' => $item, 'fields' => $fields, 'max' => $max, 'default' => $default];
    }

    private static function icon(string $name = 'icon', string $default = 'check'): array
    {
        return ['name' => $name, 'label' => 'Icon', 'type' => 'select', 'options' => array_combine(self::ICONS, array_map('ucfirst', self::ICONS)), 'default' => $default];
    }

    /** @return array<string,array{label:string,description:string,fields:array<int,array>}> */
    public static function all(): array
    {
        static $all;
        if ($all) {
            return $all;
        }
        $t = fn (...$a) => self::t(...$a);
        $ta = fn (...$a) => self::ta(...$a);

        return $all = [
            'page_header' => ['label' => 'Page title bar', 'description' => 'A colored bar with the page heading and breadcrumb. Use it at the top of ordinary pages.', 'fields' => [
                $t('heading', 'Heading (H1)', '', 'Leave blank to use the page\'s main heading.'),
                $ta('subtitle', 'Short text under the heading'),
                ['name' => 'align', 'label' => 'Alignment', 'type' => 'select', 'options' => ['left' => 'Left', 'center' => 'Centered'], 'default' => 'left'],
                ['name' => 'tone', 'label' => 'Color', 'type' => 'select', 'options' => ['brown' => 'Dark brown', 'blue' => 'Dark blue'], 'default' => 'brown'],
                self::img('image', 'Background photo (optional)', ''),
                ['name' => 'show_quiz', 'label' => 'Show the quote quiz on this banner', 'type' => 'checkbox', 'default' => true],
            ]],
            'rich_text' => ['label' => 'Text', 'description' => 'A block of formatted text (headings, lists, links). Used for About, legal pages and any article.', 'fields' => [
                ['name' => 'html', 'label' => 'Content', 'type' => 'rich', 'default' => ''],
                ['name' => 'show_version', 'label' => 'Show the page version at the bottom (for Terms / Privacy)', 'type' => 'checkbox', 'default' => false],
                $t('button_label', 'Button under the text (opens the quote pop-up)', '', 'Leave blank for no button.'),
            ]],
            'hero_video_quiz' => ['label' => 'Hero: video and quiz', 'description' => 'Full-screen hero with background video poster, heading, buttons, trust badges and the inquiry quiz.', 'fields' => [
                $t('eyebrow', 'Small label', 'The Netherlands Collection'),
                $t('heading', 'Heading (H1)', 'Discover the Authentic Netherlands'),
                $ta('subheading', 'Sub-heading'),
                $t('primary_label', 'Main button (opens the quote pop-up)', 'Get a free quote'),
                $t('secondary_label', 'Second button label', 'Explore packages'),
                self::link('secondary_url', 'Second button link', '/packages'),
                self::rep('badges', 'Trust badges (up to 3)', 'badge', [$t('text', 'Badge text')], 3),
                self::img('poster', 'Hero photo (shown first and on phones; the video plays over it on desktop)', 'images/hero-home.webp'),
                $t('chips_label', 'Label above the chips (bottom of the hero)', 'Where to?'),
                self::rep('chips', 'Destination chips (sliding row at the bottom of the hero)', 'chip', [$t('label', 'Label'), self::link('url', 'Link', '/destinations', 'A page path such as /destinations or a full https:// address.')], 12),
                ['name' => 'show_video', 'label' => 'Play the background video on desktop (untick for a photo only)', 'type' => 'checkbox', 'default' => true],
            ]],
            'partners' => ['label' => 'Partner logos', 'description' => 'A navy band with a heading, a short text and a row of partner logos. White or transparent logos look best on the dark background.', 'fields' => [
                $t('eyebrow', 'Small label', 'Travel partners'),
                $t('heading', 'Heading', 'Our Best Trusted *Partners*'),
                $ta('text', 'Text', 'Our journeys are supported by trusted partners who share our commitment to exceptional service, seamless travel and unforgettable experiences.'),
                self::rep('items', 'Logos', 'logo', [self::img('image', 'Logo'), self::link('url', 'Link (optional)', '', 'A full https:// address or a page path. Leave blank for no link.')], 8),
            ]],
            'promo_band' => ['label' => 'Offer / reassurance band', 'description' => 'A light band with a big heading, a short paragraph, up to three ticked points and a button. Good for an offer or a promise.', 'fields' => [
                $t('eyebrow', 'Small label', 'Plan with confidence'),
                $t('heading', 'Heading', 'Start planning and get your *free quote.*'),
                $ta('text', 'Text', 'Tell us what you have in mind and a travel specialist will reply with a clear, personalized quote. No pressure and no obligation.'),
                self::rep('points', 'Ticked points (up to 3)', 'point', [$t('text', 'Text')], 3),
                $t('button_label', 'Button (opens the quote pop-up)', 'Get my free quote'),
            ]],
            'tour_packages' => ['label' => 'Packages of this page (tour type or destination)', 'description' => 'A carousel with the published packages of the tour type or destination this page belongs to (used on the Self Drive, Chauffeur Driven, Coach Tours and destination pages). On any other page it shows the featured packages.', 'fields' => [
                $t('eyebrow', 'Small label', 'Signature journeys'),
                $t('heading', 'Heading', 'Our packages'),
                $ta('intro', 'Intro text', ''),
                $t('button_label', 'Button under the packages', 'See all packages'),
                $t('empty_text', 'Text when there are no packages yet', 'No packages are published yet.'),
            ]],
            'ticker' => ['label' => 'Highlights ticker', 'description' => 'A thin scrolling strip of short selling points.', 'fields' => [
                self::rep('items', 'Highlights', 'highlight', [$t('text', 'Text')], 16),
            ]],
            'value_strip' => ['label' => 'Value strip (4 icon points)', 'description' => 'A clean row of up to four short selling points, each with an icon. Sits right under the hero.', 'fields' => [
                self::rep('items', 'Points', 'point', [self::icon('icon', 'check'), $t('title', 'Title'), $ta('text', 'Short text')], 4),
            ]],
            'skyline_divider' => ['label' => 'Skyline divider', 'description' => 'A decorative Dutch skyline (canal houses and windmills) that separates a white section from a sand one. No settings.', 'fields' => []],
            'contact_bar' => ['label' => 'Contact bar', 'description' => 'A slim white bar under the hero: a line of text with the phone number from Admin > Settings, and a quote button.', 'fields' => [
                $t('text', 'Text before the phone number', 'Talk to a Netherlands travel specialist'),
                $t('button_label', 'Button (opens the quote pop-up)', 'Request a quote'),
            ]],
            'quiz_band' => ['label' => 'Quote quiz', 'description' => 'The main step-by-step quote quiz (the same one as in the home page banner) in its own section.', 'fields' => [
                $t('eyebrow', 'Small label', 'Free quote'),
                $t('heading', 'Heading', 'Plan your *Netherlands* trip'),
                $ta('text', 'Text under the heading', 'Answer a few quick questions and a travel specialist will send you a tailored quote.'),
            ]],
            'text_image' => ['label' => 'Text and photo', 'description' => 'A heading, an optional highlighted quote, paragraphs and a button beside one large photo. Choose which side the photo is on.', 'fields' => [
                ['name' => 'image_side', 'label' => 'Photo side', 'type' => 'select', 'options' => ['right' => 'Photo on the right', 'left' => 'Photo on the left'], 'default' => 'right'],
                ['name' => 'background', 'label' => 'Background', 'type' => 'select', 'options' => ['sand' => 'Light sand', 'white' => 'White'], 'default' => 'sand'],
                self::img('image', 'Photo', 'images/Intro_image_split.webp'),
                $t('heading', 'Heading'),
                $t('quote', 'Highlighted line (optional)'),
                $ta('text', 'Text', '', 'Leave a blank line between paragraphs.'),
                $t('button_label', 'Button (opens the quote pop-up)', ''),
            ]],
            'split_list' => ['label' => 'Intro: photo and ticked list', 'description' => 'A large photo (with an optional badge and a smaller second photo) on one side, a heading and a ticked list of reasons on the other.', 'fields' => [
                self::img('image1', 'Photo 1', 'images/Intro_image_split.webp'),
                self::img('image2', 'Photo 2', 'images/Intro_image_split1.webp'),
                $t('badge', 'Floating badge on the photos', 'Free, no-obligation quote'),
                $t('eyebrow', 'Small label', 'Why book with us'),
                $t('heading', 'Heading', 'The real Netherlands, *crafted around you.*'),
                self::rep('items', 'Numbered reasons', 'reason', [$t('title', 'Title'), $ta('text', 'Text')], 8),
                $t('button_label', 'Button (opens the quote pop-up)', 'Get a free quote'),
            ]],
            'packages_tabs' => ['label' => 'Featured packages', 'description' => 'Your featured packages with tabs by tour type. Choose which packages are featured in Admin > Packages.', 'fields' => [
                $t('eyebrow', 'Small label', 'Signature journeys'),
                $t('heading', 'Heading', 'Signature collection'),
                ['name' => 'layout', 'label' => 'Layout', 'type' => 'select', 'options' => ['by_type' => 'One row per tour type', 'tabs' => 'One carousel with tabs'], 'default' => 'by_type'],
                $ta('intro', 'Intro text under the heading', 'Every journey is fully customizable. These are our most loved itineraries, perfected over years of delighting travelers.'),
                $t('more_label', 'Per-row button (one row per tour type)', 'View more packages'),
                $t('all_label', 'First tab label', 'All'),
                ['name' => 'background', 'label' => 'Background', 'type' => 'select', 'options' => ['sand' => 'Light sand', 'white' => 'White'], 'default' => 'sand'],
                $t('button_label', 'Button under the packages', 'See all packages'),
            ]],
            'feature_band' => ['label' => 'Full-width photo band', 'description' => 'A wide photo with a heading, checklist and buttons on top.', 'fields' => [
                self::img('image', 'Background photo', 'images/feature_band.webp'),
                $t('eyebrow', 'Small label', 'Our promise'),
                $t('heading', 'Heading', 'Authentic, personal, unhurried'),
                self::rep('bullets', 'Checklist', 'point', [$t('text', 'Text')], 8),
                $t('primary_label', 'Main button (opens the quote pop-up)', 'Plan my trip'),
                $t('secondary_label', 'Second button label', 'See packages'),
                self::link('secondary_url', 'Second button link', '/packages'),
            ]],
            'tour_types' => ['label' => 'Tour types', 'description' => 'Cards for each active tour type (managed in Admin > Tour types).', 'fields' => [
                $t('eyebrow', 'Small label', 'Tour types'),
                $t('heading', 'Heading', 'Choose How You Want to Explore'),
                $t('quote_label', 'Quote link label', 'Get a quote'),
                $t('ask_label', 'Question link label', 'Ask us a question'),
            ]],
            'video_feature' => ['label' => 'Video feature', 'description' => 'A dark band with text and a click-to-play video.', 'fields' => [
                $t('eyebrow', 'Small label', 'Watch'),
                $t('heading', 'Heading', 'See the Netherlands *before you go*'),
                $ta('text', 'Text', 'Windmills, tulip fields, cheese markets and hidden corners, seen the small-group way.'),
                self::img('poster', 'Poster image', 'images/hero-about.webp'),
                $t('video_name', 'Video file name', 'homepage_banner', 'The video file is public/videos/NAME.mp4 on the server.'),
            ]],
            'image_strip' => ['label' => 'Photo panels (journeys by traveler)', 'description' => 'Photo cards with a number, title, text and a link: a swipe row on phones, a grid on larger screens.', 'fields' => [
                $t('eyebrow', 'Small label', 'Journeys by traveler'),
                $t('heading', 'Heading', 'Find the journey that *fits you*'),
                $t('button_label', 'Panel button (opens the quote pop-up)', 'Plan this trip'),
                self::rep('items', 'Panels', 'panel', [$t('title', 'Title'), $ta('text', 'Text'), self::img('image', 'Photo')], 12),
            ]],
            'steps' => ['label' => 'Numbered steps', 'description' => 'Three or more numbered steps, such as "How it works".', 'fields' => [
                ['name' => 'style', 'label' => 'Style', 'type' => 'select', 'options' => ['timeline' => 'Connected timeline', 'cards' => 'Simple columns', 'tiles' => 'Dark numbered tiles'], 'default' => 'timeline'],
                $t('eyebrow', 'Small label', 'Simple'),
                $t('heading', 'Heading', 'How it works'),
                $ta('intro', 'Text under the heading', ''),
                $t('button_label', 'Button under the steps (opens the quote pop-up)', ''),
                self::rep('items', 'Steps', 'step', [$t('title', 'Title'), $ta('text', 'Text')], 6),
            ]],
            'testimonials' => ['label' => 'Testimonials', 'description' => 'Reviews from Admin > Testimonials.', 'fields' => [
                ['name' => 'variant', 'label' => 'Style', 'type' => 'select', 'options' => ['dark' => 'Dark blue background', 'light' => 'Light background', 'spotlight' => 'Spotlight: one review at a time, with a picker'], 'default' => 'dark'],
                $t('eyebrow', 'Small label', 'Reviews'),
                $t('heading', 'Heading', 'What travelers say'),
                ['name' => 'limit', 'label' => 'How many to show (0 = all)', 'type' => 'number', 'default' => 0],
                $t('button_label', 'Button under the reviews (opens the quote pop-up)', ''),
                ['name' => 'show_stars', 'label' => 'Show the star ratings', 'type' => 'checkbox', 'default' => true],
                ['name' => 'bg', 'label' => 'Background (dark and spotlight styles): photo, animated GIF or video', 'type' => 'media', 'default' => 'images/couples.webp', 'help' => 'Upload a JPG, PNG, WebP or GIF (up to 8 MB) or a short MP4 / WebM video (up to 25 MB). Remove it for a plain navy background. A video plays on tablets and desktops only; phones show the photo below.'],
                self::img('poster', 'Photo for phones and while a video loads (only used with a video)'),
                ['name' => 'overlay', 'label' => 'Dark tint over the background', 'type' => 'select', 'options' => ['light' => 'Light', 'medium' => 'Medium', 'strong' => 'Strong'], 'default' => 'medium'],
            ]],
            'gallery' => ['label' => 'Photo gallery', 'description' => 'A mosaic of photos that enlarge when clicked. Use 6, 12 or 18 photos for a gap-free grid.', 'fields' => [
                $t('eyebrow', 'Small label', 'Visual journey'),
                $t('heading', 'Heading', 'A glimpse of the *real Netherlands*'),
                self::rep('items', 'Photos', 'photo', [$t('caption', 'Caption'), self::img('image', 'Photo')], 24),
                $t('closing_heading', 'Text under the gallery', 'Seen enough to start planning?'),
                $t('button_label', 'Button (opens the quote pop-up)', 'Get a free quote'),
            ]],
            'text_cta' => ['label' => 'Centered text with buttons', 'description' => 'A heading, short text and up to three buttons on a light background.', 'fields' => [
                $t('heading', 'Heading', '', 'Wrap a word in *asterisks* to italicise it.'),
                $ta('text', 'Text'),
                self::rep('buttons', 'Buttons', 'button', [$t('label', 'Label'), self::link('url', 'Link')], 3),
            ]],
            'faq' => ['label' => 'FAQ list', 'description' => 'Questions from Admin > FAQs. Adds Google FAQ markup automatically.', 'fields' => [
                $t('eyebrow', 'Small label', 'FAQ'),
                $t('heading', 'Heading', 'Frequently asked questions'),
                ['name' => 'limit', 'label' => 'How many to show (0 = all)', 'type' => 'number', 'default' => 0],
                $t('help_heading', 'Help box heading', '', 'Leave blank for no help box under the questions.'),
                $t('help_text', 'Help box text'),
                $t('help_button_label', 'Help box: quote button (opens the pop-up)', 'Get a free quote'),
                $t('help_link_label', 'Help box: second button label', 'Contact us'),
                self::link('help_link_url', 'Help box: second button link', '/contact'),
            ]],
            'blog_latest' => ['label' => 'Latest blog posts', 'description' => 'The newest posts from Admin > Blog. Hidden when there are none.', 'fields' => [
                $t('eyebrow', 'Small label', 'Travel journal'),
                $t('heading', 'Heading', 'Guides and *inspiration*'),
                $ta('intro', 'Text under the heading', ''),
                ['name' => 'count', 'label' => 'How many posts', 'type' => 'number', 'default' => 2],
                $t('button_label', 'Button', 'Read all articles'),
            ]],
            'cta_banner' => ['label' => 'Call to action banner', 'description' => 'A full-width photo with a heading, text and button.', 'fields' => [
                $t('eyebrow', 'Small label', ''),
                self::img('image', 'Background photo', 'images/fina_cta_bg.webp'),
                $t('heading', 'Heading', 'Ready to discover the *real Netherlands*?'),
                $ta('text', 'Text'),
                $t('button_label', 'Button (opens the quote pop-up)', 'Start your adventure'),
                ['name' => 'show_phone', 'label' => 'Show a call button (uses the phone number in Settings)', 'type' => 'checkbox', 'default' => true],
                $t('phone_label', 'Call button text (the number is added)', 'Call'),
            ]],
            'hero_seasons' => ['label' => 'Hero: centered with season picker', 'description' => 'Centered hero with intro text, a "when to visit" picker and badges.', 'fields' => [
                self::img('image', 'Background photo', 'images/hero-about.webp'),
                $t('eyebrow', 'Small label'),
                $t('heading', 'Heading (H1)'),
                $ta('text', 'Intro text'),
                $t('button_label', 'Button (opens the quote pop-up)', 'Get your free quote'),
                $t('season_prompt', 'Question above the picker', 'When would you like to visit?'),
                self::rep('seasons', 'Picker options', 'option', [self::icon('icon', 'spring'), $t('name', 'Name'), $t('note', 'Short note')], 6),
                self::rep('badges', 'Badges', 'badge', [self::icon('icon', 'shield'), $t('text', 'Text')], 6),
            ]],
            'hero_centered' => ['label' => 'Hero: centered with destination chips', 'description' => 'Full-width photo hero with a small caps label, a large caps heading, an intro line, two buttons and a row of destination chips.', 'fields' => [
                self::img('image', 'Background photo', 'images/hero-home.webp'),
                $t('eyebrow', 'Small label', 'Premier Netherlands Tours'),
                $t('heading', 'Heading (H1)', 'Unforgettable *Netherlands* Escapes'),
                $ta('text', 'Intro line'),
                $t('primary_label', 'Main button (opens the quote pop-up)', 'Get a free quote'),
                $t('secondary_label', 'Second button label', 'Explore the Netherlands'),
                self::link('secondary_url', 'Second button link', '/destinations'),
                $t('chips_label', 'Label before the chips', 'Where to?'),
                self::rep('chips', 'Destination chips', 'chip', [$t('label', 'Label'), self::link('url', 'Link', '/destinations', 'A page path such as /destinations or a full https:// address.')], 12),
            ]],
            'stats_band' => ['label' => 'Numbers band', 'description' => 'A navy band with a small label, a heading and up to four big numbers with a caption. Only use numbers you can stand behind.', 'fields' => [
                $t('eyebrow', 'Small label', 'The difference'),
                $t('heading', 'Heading', 'Why travel *with us?*'),
                $ta('text', 'Text under the heading'),
                self::rep('items', 'Numbers', 'number', [$t('value', 'Number (e.g. 3 or 100%)'), $t('label', 'Caption')], 4),
                $t('button_label', 'Button (opens the quote pop-up)', 'Get a free quote'),
                self::img('image', 'Background photo (optional, shown under a navy overlay)'),
            ]],
            'route_map' => ['label' => 'Route map', 'description' => 'A dark card with a map of the Netherlands: the three regions and their main stops, joined by a dotted route. Every stop links to its destination page. Text on the left.', 'fields' => [
                $t('eyebrow', 'Small label', 'Plan your route'),
                $t('heading', 'Heading', 'See where your trip *could take you*'),
                $ta('text', 'Text under the heading'),
                $t('tag', 'Tag on the map (blank hides it)', 'Tulip season: April'),
                $t('button_label', 'Button (opens the quote pop-up)', 'Plan my route'),
                ['name' => 'background', 'label' => 'Background', 'type' => 'select', 'options' => ['sand' => 'Light sand', 'white' => 'White'], 'default' => 'white'],
            ]],
            'destination_cards' => ['label' => 'Destination cards', 'description' => 'A grid of cards for each active destination (managed in Admin > Destinations), each with a photo, short text and two buttons.', 'fields' => [
                $t('eyebrow', 'Small label', 'Destinations'),
                $t('heading', 'Heading', 'Explore our top *Netherlands destinations*'),
                $ta('text', 'Text under the heading'),
                $t('explore_label', 'Card button', 'Explore'),
                $t('quote_label', 'Card quote button (opens the quote pop-up)', 'Get a quote'),
                $t('all_label', 'Button under the cards', 'View more destinations'),
                ['name' => 'background', 'label' => 'Background', 'type' => 'select', 'options' => ['sand' => 'Light sand', 'white' => 'White'], 'default' => 'sand'],
                self::link('all_url', 'Link of the button under the cards', '/destinations'),
            ]],
            'feature_cards' => ['label' => 'Feature cards', 'description' => 'A row of white cards with an icon, title and text on a dark band.', 'fields' => [
                ['name' => 'style', 'label' => 'Style', 'type' => 'select', 'options' => ['cards' => 'White cards on a dark band', 'strip' => 'Slim strip with dividers'], 'default' => 'cards'],
                self::rep('items', 'Cards', 'card', [self::icon('icon', 'map'), $t('title', 'Title'), $ta('text', 'Text')], 8),
            ]],
            'split_columns' => ['label' => 'Two columns: photo and text', 'description' => 'Two side-by-side columns, each with a photo, heading and text. The right one can have a checklist.', 'fields' => [
                self::img('left_image', 'Left photo', 'images/Intro_image_split.webp'),
                $t('left_heading', 'Left heading'),
                $ta('left_text', 'Left text'),
                self::img('right_image', 'Right photo', 'images/Intro_image_split1.webp'),
                $t('right_heading', 'Right heading'),
                $ta('right_text', 'Right text'),
                self::rep('right_bullets', 'Right checklist', 'point', [$t('text', 'Text')], 10),
            ]],
            'why_band' => ['label' => 'Dark "why us" tiles with photo', 'description' => 'A dark band with a heading, a short intro, a button, reason tiles (each with an icon) and a photo.', 'fields' => [
                $t('eyebrow', 'Small label', 'Why choose us'),
                $t('heading', 'Heading'),
                $ta('intro', 'Intro text', 'Four things we promise on every trip, whether you drive yourself, travel with a chauffeur or join a small guided group.'),
                $t('button_label', 'Button (opens the quote pop-up)', 'Get a free quote'),
                ['name' => 'bg', 'label' => 'Background: photo, animated GIF or video', 'type' => 'media', 'default' => 'images/feature_band.webp', 'help' => 'Upload a JPG, PNG, WebP or GIF (up to 8 MB) or a short MP4 / WebM video (up to 25 MB, kept short and silent works best). Remove it for a plain navy background. A video plays on tablets and desktops only; phones show the photo below.'],
                self::img('poster', 'Photo for phones and while a video loads (only used with a video)'),
                ['name' => 'overlay', 'label' => 'Dark tint over the background (for readable text)', 'type' => 'select', 'options' => ['light' => 'Light', 'medium' => 'Medium', 'strong' => 'Strong'], 'default' => 'medium'],
                ['name' => 'show_numbers', 'label' => 'Show the faint 01, 02, 03 numbers on the tiles', 'type' => 'checkbox', 'default' => true],
                self::rep('items', 'Reasons', 'reason', [self::icon('icon', 'check'), $t('title', 'Title'), $ta('text', 'Text')], 8),
                self::img('image', 'Photo beside the tiles (optional)'),
            ]],
            'team' => ['label' => 'Meet the team', 'description' => 'Cards for team members. Remove the section if you do not want to show a team.', 'fields' => [
                $t('eyebrow', 'Small label', 'Our people'),
                $t('heading', 'Heading', 'Meet the *team*'),
                self::rep('items', 'Team members', 'member', [$t('name', 'Name'), $t('role', 'Role'), $ta('bio', 'Short bio'), self::img('image', 'Photo (optional)')], 12),
            ]],
            'why_us' => ['label' => 'Why choose us strip', 'description' => 'Your four selling points as a compact strip. The wording is set once in Admin > Settings ("Why choose us"), so it matches the strip on package, tour type and destination pages.', 'fields' => [
                $t('heading', 'Heading (blank = use the Settings wording)', ''),
            ]],
            'usp_proof' => ['label' => 'Selling points with proof', 'description' => 'Each selling point with a real example behind it and a link to read more. Replace the example wording with facts you can prove.', 'fields' => [
                $t('eyebrow', 'Small label', 'Our promise'),
                $t('heading', 'Heading', 'What that looks like in practice'),
                $t('proof_label', 'Label above each example', 'In practice'),
                self::rep('items', 'Selling points', 'point', [self::icon('icon', 'check'), $t('title', 'Title'), $ta('text', 'Claim'), $ta('proof', 'Proof: a real, checkable example'), $t('link_label', 'Link text'), self::link('link', 'Link')], 6),
            ]],
            'contact_details' => ['label' => 'Contact details and form', 'description' => 'Your text and contact details (from Settings) beside the inquiry form.', 'fields' => [
                ['name' => 'intro', 'label' => 'Text beside the form', 'type' => 'rich', 'default' => ''],
                self::img('specialist_image', 'Specialist photo', 'images/team-1.webp'),
                $t('specialist_name', 'Specialist name', 'Anouk de Vries'),
                $t('specialist_role', 'Specialist role', 'Senior Travel Specialist'),
                $ta('specialist_text', 'Specialist message', 'Tell me about your trip and I will help you plan it, from the first idea to the flight home.'),
                $t('reach_heading', 'Heading above the contact cards', 'How to reach us'),
                $t('call_text', 'Call card text', 'Speak to a travel specialist'),
                $t('email_text', 'Email card text', 'Send us your plans and questions'),
                $t('hours_title', 'Hours card title', 'When we are available'),
                $t('hours_text', 'Hours card text', 'Monday to Friday, 9:00 to 17:00 Central European Time (3:00 to 11:00 US Eastern)'),
                $t('explore_heading', 'Heading above the links', 'Prefer to explore first?'),
                $t('prompts_heading', 'Heading above the message starters', 'Not sure what to ask? Tap one to start your message:'),
                self::rep('prompts', 'Message starters (up to 6)', 'starter', [$t('label', 'Button text'), $t('text', 'Text added to the message')], 6, [
                    ['label' => 'Tulip season trip', 'text' => 'I would like to see the tulip fields and gardens in spring.'],
                    ['label' => 'Private chauffeur tour', 'text' => 'I am interested in a private chauffeur-driven tour.'],
                    ['label' => 'Self drive route', 'text' => 'I would like to drive myself on a planned route.'],
                    ['label' => 'Family or group trip', 'text' => 'I am planning a trip for a family or group.'],
                    ['label' => 'Something custom', 'text' => 'I have my own ideas for a trip and would like help shaping them.'],
                ]),
                $t('reassure', 'Reassurance line above the form', 'Free and no obligation. Your details stay private.'),
                $t('submit_label', 'Form button', 'Send message'),
            ]],
        ];
    }

    public static function types(): array
    {
        return array_map(fn ($s) => $s['label'], self::all());
    }

    public static function definition(string $type): ?array
    {
        return self::all()[$type] ?? null;
    }

    /** Default data for a freshly added section. */
    public static function defaults(string $type): array
    {
        $data = [];
        foreach (self::all()[$type]['fields'] ?? [] as $f) {
            $data[$f['name']] = $f['type'] === 'repeater' ? ($f['default'] ?? []) : ($f['default'] ?? ($f['type'] === 'checkbox' ? false : ''));
            if ($f['type'] === 'image') {
                $data[$f['name'].'_alt'] = '';
            }
        }

        return $data;
    }

    /** Stored data with any missing keys filled from the defaults, so views never hit an undefined key. */
    public static function resolve(string $type, array $data): array
    {
        $out = self::defaults($type);
        foreach ($data as $k => $v) {
            $out[$k] = $v;
        }
        foreach (self::all()[$type]['fields'] ?? [] as $f) {
            if ($f['type'] === 'repeater') {
                $out[$f['name']] = collect($out[$f['name']] ?? [])->map(function ($item) use ($f) {
                    $row = [];
                    foreach ($f['fields'] as $sf) {
                        $row[$sf['name']] = $item[$sf['name']] ?? ($sf['type'] === 'checkbox' ? false : '');
                        if ($sf['type'] === 'image') {
                            $row[$sf['name'].'_alt'] = $item[$sf['name'].'_alt'] ?? '';
                        }
                    }

                    return $row;
                })->values()->all();
            }
        }

        return $out;
    }

    /** Labels of photos that were saved without alt text (accessibility + SEO), e.g. ['Photo 1 of Photos']. */
    public static function missingAlt(string $type, array $data): array
    {
        $missing = [];
        foreach (self::all()[$type]['fields'] ?? [] as $f) {
            if ($f['type'] === 'image' && ! empty($data[$f['name']]) && blank($data[$f['name'].'_alt'] ?? '')) {
                $missing[] = $f['label'];
            }
            if ($f['type'] === 'repeater') {
                foreach ($f['fields'] as $sf) {
                    if ($sf['type'] !== 'image') {
                        continue;
                    }
                    foreach ((array) ($data[$f['name']] ?? []) as $j => $row) {
                        if (! empty($row[$sf['name']]) && blank($row[$sf['name'].'_alt'] ?? '')) {
                            $missing[] = ucfirst($f['item']).' '.($j + 1).' ('.$sf['label'].')';
                        }
                    }
                }
            }
        }

        return $missing;
    }

    /** Validate + clean one section's submitted form data against its schema (and store uploaded images). */
    public static function sanitize(string $type, Request $request): array
    {
        $in = (array) $request->input('data', []);
        $out = [];
        foreach (self::all()[$type]['fields'] ?? [] as $f) {
            if ($f['type'] === 'repeater') {
                $items = [];
                foreach (array_slice(array_values((array) ($in[$f['name']] ?? [])), 0, $f['max']) as $j => $item) {
                    $row = [];
                    foreach ($f['fields'] as $sf) {
                        $row += self::cleanField($sf, (array) $item, $request, 'data.'.$f['name'].'.'.$j);
                    }
                    if (array_filter($row, fn ($v, $k) => $v !== '' && $v !== false && ! str_ends_with($k, '_alt'), ARRAY_FILTER_USE_BOTH)) {
                        $items[] = $row; // drop completely empty rows
                    }
                }
                $out[$f['name']] = $items;
            } else {
                $out += self::cleanField($f, $in, $request, 'data');
            }
        }

        return $out;
    }

    private static function cleanField(array $f, array $in, Request $request, string $path): array
    {
        $n = $f['name'];
        $v = $in[$n] ?? null;

        switch ($f['type']) {
            case 'checkbox':
                return [$n => filter_var($v, FILTER_VALIDATE_BOOLEAN)];
            case 'number':
                return [$n => max(0, min(999, (int) $v))];
            case 'select':
                return [$n => array_key_exists((string) $v, $f['options']) ? (string) $v : ($f['default'] ?? array_key_first($f['options']))];
            case 'rich':
                return [$n => Str::limit(clean_html((string) $v), 30000, '')];
            case 'textarea':
                return [$n => Str::limit(trim(strip_tags((string) $v)), 2000, '')];
            case 'link':
                $v = trim(strip_tags((string) $v));

                return [$n => preg_match('#^(/[A-Za-z0-9_./?=&%\#-]*|https?://[^\s"<>]+|\#[A-Za-z0-9_-]+|tel:[+0-9 ()-]+|mailto:[^\s"<>]+)?$#', $v) ? $v : ''];
            case 'media':
                $media = (string) ($v ?? '');
                $media = preg_match('#^(images|storage|videos)/[A-Za-z0-9_./-]+$#', $media) ? $media : '';
                if (! empty($in[$n.'_remove'])) {
                    $media = '';
                }
                if ($file = $request->file($path.'.'.$n.'_file')) {
                    $ext = strtolower($file->getClientOriginalExtension());
                    $mime = (string) $file->getMimeType();
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true) && $file->getSize() <= 8 * 1024 * 1024) {
                        $media = 'storage/'.app(ImageService::class)->store($file, '')->path.'-1600.webp';
                    } elseif ($ext === 'gif' && $mime === 'image/gif' && $file->getSize() <= 8 * 1024 * 1024) {
                        $media = 'storage/'.Storage::disk('public')->putFileAs('section-media', $file, Str::random(24).'.gif');
                    } elseif (in_array($ext, ['mp4', 'webm'], true) && in_array($mime, ['video/mp4', 'video/webm'], true) && $file->getSize() <= 25 * 1024 * 1024) {
                        $media = 'storage/'.Storage::disk('public')->putFileAs('section-media', $file, Str::random(24).'.'.$ext);
                    }
                }

                return [$n => $media];
            case 'image':
                $alt = Str::limit(trim(strip_tags((string) ($in[$n.'_alt'] ?? ''))), 190, '');
                $image = (string) ($v ?? '');
                $image = preg_match('#^(images|storage)/[A-Za-z0-9_./-]+$#', $image) ? $image : '';
                if (! empty($in[$n.'_remove'])) {
                    $image = '';
                }
                if ($file = $request->file($path.'.'.$n.'_file')) {
                    if (in_array(strtolower($file->getClientOriginalExtension()), ['jpg', 'jpeg', 'png', 'webp'], true) && $file->getSize() <= 8 * 1024 * 1024) {
                        $image = 'storage/'.app(ImageService::class)->store($file, $alt)->path.'-960.webp';
                    }
                }

                return [$n => $image, $n.'_alt' => $alt];
            default: // text
                return [$n => Str::limit(trim(strip_tags((string) $v)), 300, '')];
        }
    }
}
