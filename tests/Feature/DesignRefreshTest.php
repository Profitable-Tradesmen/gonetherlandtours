<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Destination;
use App\Models\Media;
use App\Models\Package;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** The design refresh: photo hero + transparent header, packages carousel, photo-led package pages, new section styles, Destinations page. */
class DesignRefreshTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function login(): void
    {
        $this->actingAs(AdminUser::create(['name' => 'T', 'email' => 'design@test.local', 'password' => 'test-password-123', 'role' => 'admin']), 'admin');
    }

    private function section(Page $page, string $type): array
    {
        return collect($page->fresh()->allSections())->firstWhere('type', $type);
    }

    public function test_hero_is_a_responsive_photo_with_a_preload_and_floats_a_transparent_header_over_it(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('images/hero-home.webp', $html);
        $this->assertStringContainsString('hero-home-800.webp 800w', $html, 'phones get the small copy');
        $this->assertMatchesRegularExpression('/<link rel="preload" as="image"[^>]+imagesrcset=/', $html);
        $this->assertStringContainsString('hdr-overlay', $html, 'transparent over the hero until scrolled');
        $this->assertStringContainsString('hdr-logo', $html, 'the logo is white while the header is transparent and full color on the white bar');
        $this->assertStringContainsString('bg-white/95 backdrop-blur', $html, 'white bar once scrolled');
        $this->assertStringNotContainsString('hdr-plate', $html);
        $this->assertStringNotContainsString('hero-video-poster', $html, 'the abstract placeholder illustration is gone');
    }

    public function test_header_is_solid_on_pages_without_a_hero_and_floats_on_pages_that_start_with_one(): void
    {
        foreach (['/', '/about', '/packages/sample-tulip-season-self-drive'] as $url) {
            $this->assertStringContainsString('hdr-overlay', $this->get($url)->getContent(), $url);
        }
        foreach (['/contact', '/faq', '/packages', '/blog'] as $url) {
            $this->assertStringNotContainsString('hdr-overlay', $this->get($url)->getContent(), $url);
        }
    }

    public function test_top_bar_shows_placeholder_phone_and_email_until_settings_are_filled(): void
    {
        $html = $this->get('/')->getContent();
        $this->assertStringContainsString('+1 000-000-0000', $html);
        $this->assertStringContainsString('info@gonetherlandtours.com', $html);
    }

    public function test_reference_layout_home_order_value_strip_journey_cards_and_packages_by_tour_type(): void
    {
        $home = Page::bySlug('home');
        $types = collect($home->visibleSections())->pluck('type')->all();
        $this->assertSame(['hero_video_quiz', 'value_strip', 'split_list', 'tour_types', 'packages_tabs', 'why_band', 'usp_proof'], array_slice($types, 0, 7));

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('Clear, upfront quotes', $html);
        $this->assertStringContainsString('Why we are the', $html);
        // the packages section is three plain columns of cards (no per-column heading block)
        $this->assertStringContainsString('Sample: Tulip Season Self Drive', $html);
        // the three small bars under each section label
        $this->assertStringContainsString('h-[3px] w-7', $html);
    }

    public function test_header_menu_follows_the_reference_and_meet_the_team_page_exists(): void
    {
        $html = $this->get('/')->getContent();
        preg_match('#<nav class="hidden[^>]*aria-label="Main">(.*?)</nav>#s', $html, $nav);
        foreach (['Home', 'Self Drive', 'Chauffeur Driven', 'Coach Tours', 'Destinations', 'About Us'] as $label) {
            $this->assertStringContainsString($label, $nav[1], $label);
        }
        $this->get('/meet-the-team')->assertOk()->assertSee('Meet the Team');
        $this->assertStringContainsString('/meet-the-team', $html, 'linked from the About Us dropdown');
    }

    public function test_menu_migration_switches_only_the_seeded_menu(): void
    {
        $migration = require base_path('database/migrations/2026_11_23_000002_reference_menu_and_team_page.php');

        // an admin-customised menu is left alone
        \App\Models\MenuItem::where('menu', 'header')->whereNull('parent_id')->where('label', 'Home')->update(['label' => 'Start here']);
        $migration->up();
        $this->assertTrue(\App\Models\MenuItem::where('menu', 'header')->where('label', 'Start here')->exists());

        // the old seeded menu is replaced by the reference one
        \Illuminate\Support\Facades\DB::table('menu_items')->where('menu', 'header')->delete();
        $now = now();
        foreach ([['Tours', 'tours'], ['Destinations', 'link'], ['Packages', 'link'], ['About Us', 'cards'], ['Contact', 'link']] as $i => [$label, $kind]) {
            \Illuminate\Support\Facades\DB::table('menu_items')->insert(['menu' => 'header', 'label' => $label, 'kind' => $kind, 'url' => '/x', 'sort_order' => $i + 1, 'is_active' => true, 'new_tab' => false, 'created_at' => $now, 'updated_at' => $now]);
        }
        $migration->up();
        $this->assertSame(['Home', 'Self Drive', 'Chauffeur Driven', 'Coach Tours', 'Destinations', 'About Us'], \App\Models\MenuItem::where('menu', 'header')->whereNull('parent_id')->orderBy('sort_order')->pluck('label')->all());
    }

    public function test_home_migration_reorders_only_the_previously_seeded_home(): void
    {
        $home = Page::bySlug('home');
        $current = $home->allSections();
        $by = collect($current)->keyBy('type');
        $old = [$by['hero_video_quiz'], ['id' => 'tick', 'type' => 'ticker', 'visible' => true, 'data' => ['items' => []]], $by['split_list'], $by['packages_tabs'],
            ['id' => 'band', 'type' => 'feature_band', 'visible' => true, 'data' => []], $by['usp_proof'], $by['tour_types'], $by['video_feature'], $by['image_strip'], $by['steps'],
            $by['testimonials'], $by['gallery'], $by['text_cta'], $by['faq'], $by['blog_latest'], $by['cta_banner']];
        $migration = require base_path('database/migrations/2026_11_23_000001_reference_home_layout.php');

        $home->update(['sections' => $old]);
        $migration->up();
        $types = collect($home->fresh()->allSections())->pluck('type')->all();
        $this->assertSame(['hero_video_quiz', 'value_strip', 'split_list', 'tour_types', 'packages_tabs', 'why_band', 'usp_proof'], array_slice($types, 0, 7));
        $this->assertSame(['ticker', 'feature_band'], array_slice($types, -2), 'old sections are kept, hidden');
        $this->assertSame([false, false], collect($home->fresh()->allSections())->take(-2)->pluck('visible')->all());

        // an admin who re-arranged the page: untouched
        $custom = array_reverse($old);
        $home->update(['sections' => $custom]);
        $migration->up();
        $this->assertSame(array_column($custom, 'type'), collect($home->fresh()->allSections())->pluck('type')->all());
    }

    public function test_top_bar_is_hidden_on_phones_and_h1_is_smaller(): void
    {
        $html = $this->get('/')->getContent();
        $this->assertMatchesRegularExpression('/class="hidden bg-brand-900 text-white text-xs sm:block/', $html);
        $this->assertStringContainsString('lg:text-6xl', $html);
        $this->assertStringNotContainsString('lg:text-7xl', $html);
    }

    public function test_signature_collection_is_a_carousel_row_per_tour_type_with_dashes_and_the_section_order_is_right(): void
    {
        $types = collect(Page::bySlug('home')->visibleSections())->pluck('type')->all();
        $this->assertSame(['split_list', 'tour_types', 'packages_tabs'], array_slice($types, 2, 3), 'Why book with us, Tour types, Signature Collection');

        $html = $this->get('/')->getContent();
        $this->assertStringContainsString('Signature Collection', $html);
        $this->assertStringContainsString('Self Drive Packages', $html);
        $this->assertStringContainsString('Chauffeur Driven Packages', $html);
        $this->assertGreaterThanOrEqual(3, substr_count($html, 'View more packages'));
        $this->assertGreaterThanOrEqual(3, substr_count($html, 'x-data="carousel"'));
        $this->assertStringContainsString('carousel-track', $html);
        $this->assertStringContainsString('aria-label="Next packages"', $html);
        $this->assertStringContainsString('Show package', $html, 'dash indicators');
        $this->assertStringContainsString('w-[88%]', $html, 'phones: one wide horizontal card per slide, the next one peeking');
    }

    public function test_package_card_is_a_horizontal_navy_card_on_phones_and_a_white_card_from_640px(): void
    {
        $html = $this->get('/')->getContent();
        $this->assertStringContainsString('flex-row overflow-hidden rounded-2xl bg-brand-900 text-white shadow-card sm:flex-col sm:bg-white sm:text-ink', $html);
        $this->assertStringContainsString('More info', $html, 'phones: underlined More info link');
        $this->assertStringContainsString('sm:hidden', $html);
    }

    public function test_featured_packages_tabs_layout_is_a_carousel_with_icon_tabs_and_stacked_cards(): void
    {
        $this->login();
        $home = Page::bySlug('home');
        $sec = $this->section($home, 'packages_tabs');
        $this->put(route('admin.pages.sections.update', [$home, $sec['id']]), ['data' => ['layout' => 'tabs', 'heading' => 'Packages', 'all_label' => 'All', 'button_label' => 'See all']])->assertSessionHasNoErrors();
        $html = $this->get('/')->getContent();
        $this->assertStringContainsString('x-data="carousel"', $html);
        $this->assertStringContainsString('carousel-track', $html);
        $this->assertStringContainsString('aria-label="Next packages"', $html);
        $this->assertStringContainsString('role="tablist"', $html);
        // cards: chips for nights/type, two actions in a grid from 640px
        $this->assertStringContainsString('rounded-full bg-accent px-2.5 py-1 text-ink', $html);
        $this->assertStringContainsString('sm:grid sm:grid-cols-2', $html);
    }

    public function test_package_page_has_photo_hero_pills_highlight_cards_and_day_cards(): void
    {
        $html = $this->get('/packages/sample-tulip-season-self-drive')->assertOk()->getContent();
        $this->assertStringContainsString('bg-brown pb-10 pt-28', $html);
        $this->assertStringContainsString('7 nights', $html);
        $this->assertStringContainsString('Day by day', $html);
        $this->assertStringContainsString('rounded-xl bg-sand p-4 font-semibold', $html, 'highlights are cards');
        $this->assertSame(1, preg_match_all('/<h1[\s>]/', $html));
    }

    public function test_itinerary_day_can_show_one_of_the_packages_own_photos_only(): void
    {
        $this->login();
        $package = Package::where('slug', 'sample-tulip-season-self-drive')->firstOrFail();
        $mine = Media::create(['path' => 'media/2026/own-photo', 'alt' => 'Own photo', 'width' => 960, 'height' => 640, 'widths' => [480, 960]]);
        $foreign = Media::create(['path' => 'media/2026/other-photo', 'alt' => 'Other', 'width' => 960, 'height' => 640, 'widths' => [480, 960]]);
        $package->images()->attach($mine->id, ['sort_order' => 1]);

        $this->put(route('admin.packages.update', $package), [
            'title' => $package->title, 'nights' => 7, 'currency' => 'EUR', 'is_published' => '1',
            'itinerary' => [
                ['title' => 'Day 1: Arrive', 'body' => 'Welcome', 'image' => (string) $mine->id],
                ['title' => 'Day 2: Fields', 'body' => 'Tulips', 'image' => (string) $foreign->id],
            ],
        ])->assertSessionHasNoErrors();

        $it = $package->fresh()->itinerary;
        $this->assertSame($mine->id, $it[0]['image']);
        $this->assertNull($it[1]['image'], 'a photo that is not in this package gallery is dropped');

        $html = $this->get('/packages/sample-tulip-season-self-drive')->getContent();
        $this->assertStringContainsString('own-photo-', $html);
        $this->assertStringNotContainsString('other-photo-', $html);
    }

    public function test_steps_support_timeline_and_simple_styles(): void
    {
        $this->login();
        $home = Page::bySlug('home');
        $steps = $this->section($home, 'steps');

        $this->assertSame('timeline', \App\Support\Sections::resolve('steps', $steps['data'])['style'], 'timeline is the default');
        $html = $this->get('/')->getContent();
        $this->assertStringContainsString('border-dashed border-accent', $html);

        $this->put(route('admin.pages.sections.update', [$home, $steps['id']]), ['data' => ['style' => 'cards', 'heading' => 'How', 'items' => [['title' => 'One', 'text' => 'a']]]]);
        $this->assertStringNotContainsString('ring-4 ring-white', $this->get('/')->getContent());
    }

    public function test_testimonials_spotlight_has_a_picker_and_all_reviews_in_the_html(): void
    {
        $html = $this->get('/')->getContent();
        $this->assertStringContainsString('aria-label="Next review"', $html);
        $this->assertStringContainsString('aria-label="Previous review"', $html);
        $this->assertGreaterThanOrEqual(3, substr_count($html, 'aria-label="Show review'));
        // reviews share one grid cell (no height jump) and fade via the .review-fig rule; without JS only the first shows
        $this->assertGreaterThanOrEqual(3, substr_count($html, 'class="review-fig col-start-1 row-start-1'));
        $this->assertSame(1, substr_count($html, 'review-fig col-start-1 row-start-1 is-active'), 'only the first review is active before JavaScript runs');
        $this->assertStringContainsString('.review-fig.is-active', file_get_contents(base_path('resources/css/app.css')));
        $this->assertStringContainsString('Sample review', $html);
    }

    public function test_destinations_index_lists_destinations_and_is_in_menu_and_sitemap(): void
    {
        $html = $this->get('/destinations')->assertOk()->getContent();
        $this->assertSame(1, preg_match_all('/<h1[\s>]/', $html));
        foreach (Destination::active()->get() as $d) {
            $this->assertStringContainsString(route('destinations.show', $d), $html);
        }
        $this->assertStringContainsString('<link rel="canonical" href="'.url('/destinations').'"', $html);
        $this->assertStringContainsString('/destinations', $this->get('/sitemap.xml')->getContent());
        $this->assertStringContainsString('>Destinations<', $this->get('/')->getContent(), 'header menu link');
    }

    public function test_touch_targets_for_text_links_are_at_least_44px_tall(): void
    {
        $html = $this->get('/')->getContent();
        $this->assertStringContainsString('inline-flex min-h-[2.75rem] items-center', $html);
        $this->assertStringContainsString('inline-block py-1.5 hover:text-accent', $html, 'footer links');
    }

    public function test_blog_is_a_card_in_the_about_us_dropdown_not_a_top_level_link(): void
    {
        $about = \App\Models\MenuItem::where('menu', 'header')->whereNull('parent_id')->where('kind', 'cards')->firstOrFail();
        $card = \App\Models\MenuItem::where('parent_id', $about->id)->where('url', '/blog')->first();
        $this->assertNotNull($card, 'Blog is a child of About Us');
        $this->assertSame(0, \App\Models\MenuItem::where('menu', 'header')->whereNull('parent_id')->where('url', '/blog')->count());

        $html = $this->get('/')->getContent();
        preg_match('#<nav class="hidden[^>]*aria-label="Main">(.*?)</nav>#s', $html, $nav);
        $this->assertStringNotContainsString('Blog', $nav[1], 'no top-level Blog link in the main nav');

        $this->assertStringContainsString('Travel guides and inspiration', $html);
        $dropdown = strstr($html, 'id="mega-'.$about->id.'"');
        $this->assertNotFalse($dropdown, 'About Us dropdown panel exists');
        $panel = strstr($dropdown, 'id="mobile-menu"', true) ?: $dropdown;   // the dropdown ends where the mobile menu starts
        $this->assertStringContainsString(route('blog.index'), $panel);
        // phones: Blog is listed under About Us in the mobile menu
        $this->assertMatchesRegularExpression('#id="mobile-menu".*About Us.*Blog#s', $html);
    }

    public function test_migration_moves_a_standalone_blog_link_under_about_us_and_leaves_custom_menus_alone(): void
    {
        $about = \App\Models\MenuItem::where('menu', 'header')->whereNull('parent_id')->where('kind', 'cards')->firstOrFail();
        $blog = \App\Models\MenuItem::where('url', '/blog')->where('parent_id', $about->id)->firstOrFail();
        $migration = require base_path('database/migrations/2026_11_20_000001_move_blog_under_about_menu.php');

        // old layout: Blog as a top-level link
        $blog->update(['parent_id' => null, 'description' => null, 'image' => null, 'sort_order' => 99]);
        $migration->up();
        $blog->refresh();
        $this->assertSame($about->id, $blog->parent_id);
        $this->assertNotEmpty($blog->image);
        $this->assertGreaterThan(0, $blog->sort_order);

        // an admin who turned the dropdown into a plain link: nothing happens
        $blog->update(['parent_id' => null]);
        $about->update(['kind' => 'link']);
        $migration->up();
        $this->assertNull($blog->fresh()->parent_id);
    }

    public function test_why_band_has_intro_button_numbered_icon_tiles_and_updated_content(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('Why we are the', $html);
        $this->assertStringContainsString('Four things we promise on every trip', $html);
        foreach (['Real Dutch culture', 'Private or small group', 'Hidden gems, local secrets', 'Itineraries built around you'] as $title) {
            $this->assertStringContainsString($title, $html, $title);
        }
        $this->assertStringContainsString('Coach tours are small guided groups', $html, 'accurate small-group claim');
        $this->assertStringContainsString('>01<', $html);
    }

    public function test_migration_refreshes_only_the_untouched_why_band_reasons(): void
    {
        $migration = require base_path('database/migrations/2026_11_24_000004_refresh_why_band_content.php');
        $home = Page::bySlug('home');
        $sections = $home->allSections();
        foreach ($sections as &$s) {
            if ($s['type'] === 'why_band') {
                $s['data']['items'] = [
                    ['title' => 'Authentic Dutch culture', 'text' => 'Windmills, tulip fields, cheese markets and local traditions.'],
                    ['title' => 'Hidden gems & local secrets', 'text' => 'My own wording about hidden places.'],
                ];
            }
        }
        unset($s);
        $home->update(['sections' => $sections]);

        $migration->up();
        $items = collect($home->fresh()->allSections())->firstWhere('type', 'why_band')['data']['items'];
        $this->assertSame(['tulip', 'Real Dutch culture'], [$items[0]['icon'], $items[0]['title']]);
        $this->assertSame('My own wording about hidden places.', $items[1]['text'], 'edited text is never replaced');
    }

    public function test_package_card_and_row_wording_is_editable_in_settings(): void
    {
        $this->login();
        $this->assertStringContainsString('Package cards and carousels', $this->get(route('admin.settings'))->assertOk()->getContent());

        \App\Models\Setting::put('card_quote_label', 'Request a quote');
        \App\Models\Setting::put('card_details_label', 'Full itinerary');
        \App\Models\Setting::put('row_packages_suffix', 'Journeys');
        \App\Models\Setting::put('card_sample_badge', 'Demo');
        $html = $this->get('/')->getContent();
        $this->assertStringContainsString('Request a quote', $html);
        $this->assertStringContainsString('Full itinerary', $html);
        $this->assertStringContainsString('Self Drive Journeys', $html);
        $this->assertStringContainsString('>Demo<', $html);
        $this->assertStringNotContainsString('Self Drive Packages', $html);

        \App\Models\Setting::put('row_packages_suffix', '');
        $this->assertStringContainsString('</span>Self Drive</h3>', $this->get('/')->getContent(), 'blank suffix shows just the tour type name');
    }

    public function test_why_band_numbers_can_be_switched_off(): void
    {
        $this->login();
        $home = Page::bySlug('home');
        $band = $this->section($home, 'why_band');
        $mark = 'font-display text-4xl font-medium text-white/10';
        $this->assertStringContainsString($mark, $this->get('/')->getContent());
        $this->put(route('admin.pages.sections.update', [$home, $band['id']]), ['data' => ['heading' => 'Why us', 'items' => [['icon' => 'check', 'title' => 'A', 'text' => 'b']]]])->assertSessionHasNoErrors();
        $this->assertStringNotContainsString($mark, $this->get('/')->getContent());
    }

    public function test_migration_moves_tour_types_above_the_signature_collection(): void
    {
        $migration = require base_path('database/migrations/2026_11_24_000002_move_tour_types_above_signature_collection.php');
        $home = Page::bySlug('home');
        $secs = collect($home->allSections());
        $tt = $secs->firstWhere('type', 'tour_types');
        $swapped = $secs->reject(fn ($s) => $s['type'] === 'tour_types')->values()->all();
        $at = array_search('packages_tabs', array_column($swapped, 'type'), true);
        array_splice($swapped, $at + 1, 0, [$tt]);   // old order: packages first, tour types after
        $home->update(['sections' => $swapped]);

        $migration->up();
        $types = collect($home->fresh()->allSections())->pluck('type')->all();
        $this->assertSame(array_search('packages_tabs', $types, true) - 1, array_search('tour_types', $types, true));

        $before = $types;
        $migration->up();
        $this->assertSame($before, collect($home->fresh()->allSections())->pluck('type')->all(), 'running it again changes nothing');
    }
}
