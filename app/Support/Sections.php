<?php

namespace App\Support;

use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * The page builder's section catalogue. Each section type = a Blade view (resources/views/sections/{type}.blade.php)
 * + a field schema that drives the admin form, validation/sanitising and defaults.
 *
 * Field types: text, textarea, rich, link, image, select, checkbox, number, repeater.
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
            'page_header' => ['label' => 'Page title bar', 'description' => 'A coloured bar with the page heading and breadcrumb. Use it at the top of ordinary pages.', 'fields' => [
                $t('heading', 'Heading (H1)', '', 'Leave blank to use the page\'s main heading.'),
                $ta('subtitle', 'Short text under the heading'),
                ['name' => 'align', 'label' => 'Alignment', 'type' => 'select', 'options' => ['left' => 'Left', 'center' => 'Centred'], 'default' => 'left'],
                ['name' => 'tone', 'label' => 'Colour', 'type' => 'select', 'options' => ['brown' => 'Dark brown', 'blue' => 'Dark blue'], 'default' => 'brown'],
            ]],
            'rich_text' => ['label' => 'Text', 'description' => 'A block of formatted text (headings, lists, links). Used for About, legal pages and any article.', 'fields' => [
                ['name' => 'html', 'label' => 'Content', 'type' => 'rich', 'default' => ''],
                ['name' => 'show_version', 'label' => 'Show the page version at the bottom (for Terms / Privacy)', 'type' => 'checkbox', 'default' => false],
                $t('button_label', 'Button under the text (opens the quote pop-up)', '', 'Leave blank for no button.'),
            ]],
            'hero_video_quiz' => ['label' => 'Hero: video and quiz', 'description' => 'Full-screen hero with background video poster, heading, buttons, trust badges and the enquiry quiz.', 'fields' => [
                $t('eyebrow', 'Small label', 'The Netherlands Collection'),
                $t('heading', 'Heading (H1)', 'Discover the Authentic Netherlands'),
                $ta('subheading', 'Sub-heading'),
                $t('primary_label', 'Main button (opens the quote pop-up)', 'Get a free quote'),
                $t('secondary_label', 'Second button label', 'Explore packages'),
                self::link('secondary_url', 'Second button link', '/packages'),
                self::rep('badges', 'Trust badges (up to 3)', 'badge', [$t('text', 'Badge text')], 3),
                self::img('poster', 'Hero image shown before the video loads', 'images/hero-video-poster.webp'),
            ]],
            'ticker' => ['label' => 'Highlights ticker', 'description' => 'A thin scrolling strip of short selling points.', 'fields' => [
                self::rep('items', 'Highlights', 'highlight', [$t('text', 'Text')], 16),
            ]],
            'split_list' => ['label' => 'Intro: photos and numbered list', 'description' => 'Two photos on one side, a heading and a numbered list of reasons on the other.', 'fields' => [
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
                $t('heading', 'Heading', 'Signature Netherlands Journeys'),
                $t('all_label', 'First tab label', 'All'),
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
                $t('quote_label', 'Quote link label', 'Get a Quote'),
                $t('ask_label', 'Question link label', 'Ask Us a Question'),
            ]],
            'video_feature' => ['label' => 'Video feature', 'description' => 'A dark band with text and a click-to-play video.', 'fields' => [
                $t('eyebrow', 'Small label', 'Watch'),
                $t('heading', 'Heading', 'See the Netherlands *before you go*'),
                $ta('text', 'Text', 'Windmills, tulip fields, cheese markets and hidden corners, seen the small-group way.'),
                self::img('poster', 'Poster image', 'images/hero-video-poster.webp'),
                $t('video_name', 'Video file name', 'homepage_banner', 'The video file is public/videos/NAME.mp4 on the server.'),
            ]],
            'image_strip' => ['label' => 'Photo panels (journeys by traveller)', 'description' => 'Swipeable photo cards on phones, an expanding strip of panels on desktop.', 'fields' => [
                $t('eyebrow', 'Small label', 'Journeys by traveller'),
                $t('heading', 'Heading', 'Find the journey that *fits you*'),
                $t('button_label', 'Panel button (opens the quote pop-up)', 'Plan this trip'),
                self::rep('items', 'Panels', 'panel', [$t('title', 'Title'), $ta('text', 'Text'), self::img('image', 'Photo')], 12),
            ]],
            'steps' => ['label' => 'Numbered steps', 'description' => 'Three or more numbered steps, such as "How it works".', 'fields' => [
                $t('eyebrow', 'Small label', 'Simple'),
                $t('heading', 'Heading', 'How It Works'),
                self::rep('items', 'Steps', 'step', [$t('title', 'Title'), $ta('text', 'Text')], 6),
            ]],
            'testimonials' => ['label' => 'Testimonials', 'description' => 'Reviews from Admin > Testimonials.', 'fields' => [
                ['name' => 'variant', 'label' => 'Style', 'type' => 'select', 'options' => ['dark' => 'Dark blue background', 'light' => 'Light background'], 'default' => 'dark'],
                $t('eyebrow', 'Small label', 'Reviews'),
                $t('heading', 'Heading', 'What Travellers Say'),
                ['name' => 'limit', 'label' => 'How many to show (0 = all)', 'type' => 'number', 'default' => 0],
                $t('button_label', 'Button under the reviews (opens the quote pop-up)', ''),
            ]],
            'gallery' => ['label' => 'Photo gallery', 'description' => 'A mosaic of photos that enlarge when clicked. Use 6, 12 or 18 photos for a gap-free grid.', 'fields' => [
                $t('eyebrow', 'Small label', 'Visual journey'),
                $t('heading', 'Heading', 'A glimpse of the *real Netherlands*'),
                self::rep('items', 'Photos', 'photo', [$t('caption', 'Caption'), self::img('image', 'Photo')], 24),
                $t('closing_heading', 'Text under the gallery', 'Seen enough to start planning?'),
                $t('button_label', 'Button (opens the quote pop-up)', 'Get a free quote'),
            ]],
            'text_cta' => ['label' => 'Centred text with buttons', 'description' => 'A heading, short text and up to three buttons on a light background.', 'fields' => [
                $t('heading', 'Heading', '', 'Wrap a word in *asterisks* to italicise it.'),
                $ta('text', 'Text'),
                self::rep('buttons', 'Buttons', 'button', [$t('label', 'Label'), self::link('url', 'Link')], 3),
            ]],
            'faq' => ['label' => 'FAQ list', 'description' => 'Questions from Admin > FAQs. Adds Google FAQ markup automatically.', 'fields' => [
                $t('eyebrow', 'Small label', 'FAQ'),
                $t('heading', 'Heading', 'Frequently Asked Questions'),
                ['name' => 'limit', 'label' => 'How many to show (0 = all)', 'type' => 'number', 'default' => 0],
                $t('help_heading', 'Help box heading', '', 'Leave blank for no help box under the questions.'),
                $t('help_text', 'Help box text'),
                $t('help_button_label', 'Help box: quote button (opens the pop-up)', 'Get a Free Quote'),
                $t('help_link_label', 'Help box: second button label', 'Contact us'),
                self::link('help_link_url', 'Help box: second button link', '/contact'),
            ]],
            'blog_latest' => ['label' => 'Latest blog posts', 'description' => 'The newest posts from Admin > Blog. Hidden when there are none.', 'fields' => [
                $t('eyebrow', 'Small label', 'Travel journal'),
                $t('heading', 'Heading', 'Guides and *inspiration*'),
                ['name' => 'count', 'label' => 'How many posts', 'type' => 'number', 'default' => 2],
                $t('button_label', 'Button', 'Read all articles'),
            ]],
            'cta_banner' => ['label' => 'Call to action banner', 'description' => 'A full-width photo with a heading, text and button.', 'fields' => [
                self::img('image', 'Background photo', 'images/fina_cta_bg.webp'),
                $t('heading', 'Heading', 'Ready to Discover the Real Netherlands?'),
                $ta('text', 'Text'),
                $t('button_label', 'Button (opens the quote pop-up)', 'Start Your Adventure'),
                ['name' => 'show_phone', 'label' => 'Show a call button (uses the phone number in Settings)', 'type' => 'checkbox', 'default' => true],
                $t('phone_label', 'Call button text (the number is added)', 'Call'),
            ]],
            'hero_seasons' => ['label' => 'Hero: centred with season picker', 'description' => 'Centred hero with intro text, a "when to visit" picker and badges.', 'fields' => [
                self::img('image', 'Background photo', 'images/hero.webp'),
                $t('eyebrow', 'Small label'),
                $t('heading', 'Heading (H1)'),
                $ta('text', 'Intro text'),
                $t('button_label', 'Button (opens the quote pop-up)', 'Get Your Free Quote'),
                $t('season_prompt', 'Question above the picker', 'When would you like to visit?'),
                self::rep('seasons', 'Picker options', 'option', [self::icon('icon', 'spring'), $t('name', 'Name'), $t('note', 'Short note')], 6),
                self::rep('badges', 'Badges', 'badge', [self::icon('icon', 'shield'), $t('text', 'Text')], 6),
            ]],
            'feature_cards' => ['label' => 'Feature cards', 'description' => 'A row of white cards with an icon, title and text on a dark band.', 'fields' => [
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
            'why_band' => ['label' => 'Dark list with photo', 'description' => 'A dark band with a heading, a ticked list of reasons and a photo.', 'fields' => [
                $t('heading', 'Heading'),
                self::rep('items', 'Reasons', 'reason', [$t('title', 'Title'), $ta('text', 'Text')], 10),
                self::img('image', 'Photo', 'images/feature_band.webp'),
            ]],
            'team' => ['label' => 'Meet the team', 'description' => 'Cards for team members. Remove the section if you do not want to show a team.', 'fields' => [
                $t('eyebrow', 'Small label', 'Our people'),
                $t('heading', 'Heading', 'Meet the *team*'),
                self::rep('items', 'Team members', 'member', [$t('name', 'Name'), $t('role', 'Role'), $ta('bio', 'Short bio'), self::img('image', 'Photo (optional)')], 12),
            ]],
            'contact_details' => ['label' => 'Contact details and form', 'description' => 'Your text and contact details (from Settings) beside the enquiry form.', 'fields' => [
                ['name' => 'intro', 'label' => 'Text beside the form', 'type' => 'rich', 'default' => ''],
                $t('submit_label', 'Form button', 'Send Message'),
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
