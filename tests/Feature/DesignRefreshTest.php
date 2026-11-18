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

    public function test_hero_is_a_responsive_photo_with_a_preload_and_the_header_floats_over_it(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('images/hero-home.webp', $html);
        $this->assertStringContainsString('hero-home-800.webp 800w', $html, 'phones get the small copy');
        $this->assertMatchesRegularExpression('/<link rel="preload" as="image"[^>]+imagesrcset=/', $html);
        $this->assertStringNotContainsString('hdr-overlay', $html, 'the header no longer floats over the hero');
        $this->assertStringNotContainsString('hdr-plate', $html, 'the transparent logo sits straight on the light header, no plate');
        $this->assertStringContainsString('bg-white/95 backdrop-blur', $html);
        $this->assertStringNotContainsString('hero-video-poster', $html, 'the abstract placeholder illustration is gone');
    }

    public function test_header_is_solid_on_pages_without_a_hero_and_floats_on_pages_that_start_with_one(): void
    {
        foreach (['/', '/about', '/packages/sample-tulip-season-self-drive', '/contact', '/faq', '/packages', '/blog'] as $url) {
            $this->assertStringNotContainsString('hdr-overlay', $this->get($url)->getContent(), $url);
        }
    }

    public function test_top_bar_is_hidden_on_phones_and_h1_is_smaller(): void
    {
        $html = $this->get('/')->getContent();
        $this->assertMatchesRegularExpression('/class="hidden bg-ink text-white text-xs sm:block/', $html);
        $this->assertStringContainsString('lg:text-6xl', $html);
        $this->assertStringNotContainsString('lg:text-7xl', $html);
    }

    public function test_featured_packages_render_as_a_carousel_with_icon_tabs_and_stacked_cards(): void
    {
        $html = $this->get('/')->getContent();
        $this->assertStringContainsString('x-data="carousel"', $html);
        $this->assertStringContainsString('carousel-track', $html);
        $this->assertStringContainsString('aria-label="Next packages"', $html);
        $this->assertStringContainsString('role="tablist"', $html);
        // cards: image on top at every size (no side-by-side "flex-row"), chips for nights/type, two actions in a grid
        $this->assertStringNotContainsString('flex h-full flex-row', $html);
        $this->assertStringContainsString('rounded-full bg-accent px-2.5 py-1 text-ink', $html);
        $this->assertStringContainsString('grid grid-cols-2 gap-3 pt-5', $html);
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

    public function test_goscottish_layout_home_order_centered_hero_feature_strip_and_travel_style_cards(): void
    {
        $types = collect(Page::bySlug('home')->visibleSections())->pluck('type')->all();
        $this->assertSame(['hero_video_quiz', 'feature_strip', 'split_list', 'tour_types', 'promo_band', 'packages_tabs'], array_slice($types, 0, 6));

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('hero-in label-caps inline-flex', $html, 'centered hero with the line-and-dot label');
        $this->assertStringContainsString('max-w-2xl text-left', $html, 'the quiz sits centered under the headline');
        $this->assertStringContainsString('Clear, Upfront Quotes', $html);
        // travel-style cards: tag, three bullets and three actions
        $this->assertStringContainsString('Guided small-group tour', $html);
        $this->assertStringContainsString('Led by a local guide', $html);
        $this->assertStringContainsString('Ask Us a Question', $html);
        $this->assertStringContainsString('More Info', $html);
        // packages: one row per tour type, each with a "view more" button
        $this->assertStringContainsString('Self Drive Packages', $html);
        $this->assertGreaterThanOrEqual(3, substr_count($html, 'View more packages'));
        // heading label motif
        $this->assertStringContainsString('h-px w-7', $html);
    }

    public function test_split_hero_layout_still_available(): void
    {
        $this->login();
        $home = Page::bySlug('home');
        $hero = $this->section($home, 'hero_video_quiz');
        $this->put(route('admin.pages.sections.update', [$home, $hero['id']]), ['data' => ['layout' => 'split', 'heading' => 'Split *Hero*', 'primary_label' => 'Quote me']])->assertSessionHasNoErrors();
        $html = $this->get('/')->getContent();
        $this->assertStringContainsString('lg:grid-cols-[1.1fr_0.9fr]', $html);
        $this->assertStringContainsString('Quote me', $html);
    }

    public function test_tour_type_card_tag_and_points_are_editable_in_admin(): void
    {
        $this->login();
        $t = \App\Models\TourType::where('slug', 'self-drive')->firstOrFail();
        $this->put(route('admin.tour-types.update', $t), ['name' => $t->name, 'card_tag' => 'My own tag', 'card_points' => "One\nTwo\nThree\nFour", 'is_active' => '1'])->assertSessionHasNoErrors();
        $this->assertSame(['One', 'Two', 'Three', 'Four'], $t->fresh()->cardPointList());
        $html = $this->get('/')->getContent();
        $this->assertStringContainsString('My own tag', $html);
        $this->assertStringContainsString('Three', $html);
        $this->assertStringNotContainsString('>Four<', $html, 'only the first three points are shown');
    }

    public function test_header_has_a_dropdown_per_tour_type_listing_its_packages_and_a_meet_the_team_page(): void
    {
        $html = $this->get('/')->getContent();
        preg_match('#<nav class="hidden[^>]*aria-label="Main">(.*?)</nav>#s', $html, $nav);
        foreach (['Home', 'Self Drive', 'Chauffeur Driven', 'Coach Tours', 'About Us'] as $label) {
            $this->assertStringContainsString($label, $nav[1], $label);
        }
        $this->assertStringContainsString('aria-controls="mega-t', $nav[1], 'tour type dropdown buttons');
        $this->assertStringContainsString('Sample: Tulip Season Self Drive', $html, 'the package is listed in the Self Drive dropdown');
        $this->get('/meet-the-team')->assertOk()->assertSee('Meet the Team');
        $this->assertStringContainsString('/meet-the-team', $html);
    }

    public function test_migration_switches_only_the_seeded_menu_and_reorders_only_the_seeded_home(): void
    {
        $migration = require base_path('database/migrations/2026_11_21_000002_goscottish_layout_menu_and_team_page.php');

        // an admin-customised menu is left alone
        \App\Models\MenuItem::where('menu', 'header')->whereNull('parent_id')->where('label', 'Home')->update(['label' => 'Start here']);
        $migration->up();
        $this->assertTrue(\App\Models\MenuItem::where('menu', 'header')->where('label', 'Start here')->exists());

        // the old seeded menu is replaced
        \Illuminate\Support\Facades\DB::table('menu_items')->where('menu', 'header')->delete();
        $now = now();
        foreach ([['Tours', 'tours'], ['Destinations', 'link'], ['Packages', 'link'], ['About Us', 'cards'], ['Contact', 'link']] as $i => [$label, $kind]) {
            \Illuminate\Support\Facades\DB::table('menu_items')->insert(['menu' => 'header', 'label' => $label, 'kind' => $kind, 'url' => '/x', 'sort_order' => $i + 1, 'is_active' => true, 'new_tab' => false, 'created_at' => $now, 'updated_at' => $now]);
        }
        $migration->up();
        $this->assertSame(['Home', 'Self Drive', 'Chauffeur Driven', 'Coach Tours', 'About Us'], \App\Models\MenuItem::where('menu', 'header')->whereNull('parent_id')->orderBy('sort_order')->pluck('label')->all());

        // home page: previous order is converted (old ticker kept hidden), a re-arranged one is untouched
        $home = Page::bySlug('home');
        $by = collect($home->allSections())->keyBy('type');
        $old = [$by['hero_video_quiz'], ['id' => 'tick', 'type' => 'ticker', 'visible' => true, 'data' => ['items' => []]], $by['split_list'], $by['packages_tabs'], $by['feature_band'], $by['tour_types'],
            $by['video_feature'], $by['image_strip'], $by['steps'], $by['testimonials'], $by['gallery'], $by['text_cta'], $by['faq'], $by['blog_latest'], $by['cta_banner']];
        $home->update(['sections' => $old]);
        $migration->up();
        $now = collect($home->fresh()->allSections());
        $this->assertSame(['hero_video_quiz', 'feature_strip', 'split_list', 'tour_types', 'promo_band', 'packages_tabs'], $now->pluck('type')->take(6)->all());
        $this->assertSame(['ticker', false], [$now->last()['type'], $now->last()['visible']]);

        $custom = array_reverse($old);
        $home->update(['sections' => $custom]);
        $migration->up();
        $this->assertSame(array_column($custom, 'type'), collect($home->fresh()->allSections())->pluck('type')->all());
    }
}
