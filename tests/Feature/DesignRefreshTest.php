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
        $this->assertStringContainsString('hdr-overlay', $html);
        $this->assertStringContainsString('hdr-plate', $html, 'logo sits on a white plate while transparent');
        $this->assertStringNotContainsString('hero-video-poster', $html, 'the abstract placeholder illustration is gone');
    }

    public function test_header_is_solid_on_pages_without_a_hero_and_floats_on_pages_that_start_with_one(): void
    {
        $this->assertStringContainsString('hdr-overlay', $this->get('/about')->getContent());
        $this->assertStringContainsString('hdr-overlay', $this->get('/packages/sample-tulip-season-self-drive')->getContent());
        foreach (['/contact', '/faq', '/packages', '/blog'] as $url) {
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
}
