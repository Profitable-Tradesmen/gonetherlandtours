<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Setting;
use App\Support\Sections;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Pages are built from sections edited in Admin > Pages; menus and site text are editable too. */
class PageBuilderTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function login(string $role = 'admin'): AdminUser
    {
        $u = AdminUser::create(['name' => 'T', 'email' => $role.'-pb@test.local', 'password' => 'test-password-123', 'role' => $role]);
        $this->actingAs($u, 'admin');

        return $u;
    }

    private function section(Page $page, string $type): array
    {
        return collect($page->fresh()->allSections())->firstWhere('type', $type);
    }

    public function test_every_builtin_page_was_converted_into_sections(): void
    {
        $this->assertGreaterThanOrEqual(14, count(Page::bySlug('home')->allSections()));
        foreach (['about', 'contact', 'terms', 'privacy', 'faq', 'testimonials'] as $slug) {
            $this->assertNotEmpty(Page::bySlug($slug)->allSections(), $slug);
            $this->assertTrue(Page::bySlug($slug)->is_system);
        }
        $this->assertGreaterThan(5, MenuItem::where('menu', 'header')->count());
        $this->assertGreaterThan(5, MenuItem::where('menu', 'footer')->count());
    }

    public function test_every_section_type_renders_with_its_defaults_without_errors(): void
    {
        Page::create(['slug' => 'all-sections', 'title' => 'All sections', 'h1' => 'All sections', 'is_published' => true, 'sections' => collect(array_keys(Sections::all()))->map(fn ($t) => ['id' => 's'.$t, 'type' => $t, 'visible' => true, 'data' => Sections::defaults($t)])->all()]);
        $this->get('/all-sections')->assertOk();
    }

    public function test_editing_a_section_changes_the_public_page(): void
    {
        $this->login();
        $home = Page::bySlug('home');
        $hero = $this->section($home, 'hero_video_quiz');

        $this->get(route('admin.pages.sections.edit', [$home, $hero['id']]))->assertOk()->assertSee('Edit section: Hero: video and quiz');

        $this->put(route('admin.pages.sections.update', [$home, $hero['id']]), ['data' => ['heading' => 'Brand *New* Heading', 'subheading' => 'Fresh sub', 'primary_label' => 'Quote me', 'secondary_label' => '', 'badges' => [['text' => 'Badge A'], ['text' => '']]]])
            ->assertRedirect(route('admin.pages.edit', $home));

        $this->get('/')->assertOk()->assertSee('Brand')->assertSee('Fresh sub')->assertSee('Quote me')->assertSee('Badge A')->assertDontSee('Explore packages');
    }

    public function test_repeater_rows_are_saved_and_empty_rows_dropped(): void
    {
        $this->login();
        $home = Page::bySlug('home');
        $steps = $this->section($home, 'steps');

        $this->put(route('admin.pages.sections.update', [$home, $steps['id']]), ['data' => ['heading' => 'Our process', 'items' => [
            ['title' => 'Step one', 'text' => 'Do this'], ['title' => '', 'text' => ''], ['title' => 'Step two', 'text' => 'Then that'],
        ]]]);

        $saved = $this->section($home, 'steps')['data'];
        $this->assertCount(2, $saved['items']);
        $this->get('/')->assertSee('Our process')->assertSee('Step one')->assertSee('Then that');
    }

    public function test_input_is_sanitised(): void
    {
        $this->login();
        $home = Page::bySlug('home');
        $band = $this->section($home, 'cta_banner');
        $cta = $this->section($home, 'text_cta');

        $this->put(route('admin.pages.sections.update', [$home, $band['id']]), ['data' => ['heading' => '<script>alert(1)</script>Safe heading', 'image' => 'https://evil.test/x.jpg']]);
        $this->put(route('admin.pages.sections.update', [$home, $cta['id']]), ['data' => ['heading' => 'Go', 'buttons' => [['label' => 'Go', 'url' => 'javascript:alert(1)']]]]);

        $d = $this->section($home, 'cta_banner')['data'];
        $this->assertStringNotContainsString('<script', $d['heading']);
        $this->assertSame('', $d['image'], 'only images/ or storage/ paths are accepted');
        $this->assertSame('', $this->section($home, 'text_cta')['data']['buttons'][0]['url'], 'javascript: links are rejected');

        $legal = Page::bySlug('terms');
        $rt = $this->section($legal, 'rich_text');
        $this->put(route('admin.pages.sections.update', [$legal, $rt['id']]), ['data' => ['html' => '<p onclick="x()">Hello <script>bad()</script><strong>there</strong></p>', 'show_version' => '1']]);
        $html = $this->section($legal, 'rich_text')['data']['html'];
        $this->assertStringNotContainsString('onclick', $html);
        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringContainsString('<strong>there</strong>', $html);
    }

    public function test_sections_can_be_added_moved_hidden_and_deleted(): void
    {
        $this->login();
        $page = Page::bySlug('contact');
        $before = count($page->allSections());

        $this->post(route('admin.pages.sections.add', $page), ['type' => 'text_cta'])->assertRedirect();
        $this->post(route('admin.pages.sections.add', $page), ['type' => 'nonsense'])->assertSessionHasErrors('type');
        $this->assertCount($before + 1, $page->fresh()->allSections());

        $new = $this->section($page, 'text_cta');
        $this->post(route('admin.pages.sections.move', [$page, $new['id'], 'up']));
        $this->assertSame($new['id'], $page->fresh()->allSections()[$before - 1]['id']);

        $this->post(route('admin.pages.sections.toggle', [$page, $new['id']]));
        $this->assertFalse($this->section($page, 'text_cta')['visible']);
        $this->assertCount($before, $page->fresh()->visibleSections());

        $this->delete(route('admin.pages.sections.destroy', [$page, $new['id']]));
        $this->assertCount($before, $page->fresh()->allSections());
    }

    public function test_new_page_is_a_draft_until_published_and_then_appears_in_sitemap(): void
    {
        $admin = $this->login();
        $this->post(route('admin.pages.store'), ['title' => 'Group Travel'])->assertRedirect();
        $page = Page::where('slug', 'group-travel')->firstOrFail();

        // Admins can preview the draft (with a banner and noindex); visitors get a 404.
        $this->get('/group-travel')->assertOk()->assertSee('Draft preview')->assertSee('noindex', false);
        \Illuminate\Support\Facades\Auth::guard('admin')->logout();
        $this->get('/group-travel')->assertNotFound();
        $this->assertStringNotContainsString('/group-travel', $this->get('/sitemap.xml')->getContent());
        $this->actingAs($admin, 'admin');

        $this->put(route('admin.pages.update', $page), ['title' => 'Group Travel', 'slug' => 'group-travel', 'h1' => 'Group Travel', 'seo_title' => 'Group travel tours', 'meta_description' => 'Tours for groups.'])->assertSessionHasNoErrors();
        $this->assertFalse($page->fresh()->is_published, 'saving details must not publish');
        $this->get(route('admin.pages.edit', $page))->assertSee('This page is a DRAFT')->assertSee('Publish this page');
        $this->post(route('admin.pages.publish', $page))->assertSessionHas('status');
        $this->get(route('admin.pages.edit', $page))->assertSee('This page is LIVE');

        $html = $this->get('/group-travel')->assertOk()->assertSee('Group Travel')->assertSee('Write your page content here.')->getContent();
        $this->assertSame(1, preg_match_all('/<h1[\s>]/', $html));
        $this->assertStringContainsString('<title>Group travel tours</title>', $html);
        $this->assertStringContainsString('/group-travel', $this->get('/sitemap.xml')->getContent());
    }

    public function test_reserved_and_duplicate_addresses_are_rejected_and_builtin_pages_cannot_be_deleted(): void
    {
        $this->login();
        $this->post(route('admin.pages.store'), ['title' => 'Packages', 'slug' => 'packages']);
        $this->assertNull(Page::where('slug', 'packages')->first(), 'reserved address gets renamed');
        $this->assertNotNull(Page::where('slug', 'packages-page')->first());

        $page = Page::where('slug', 'packages-page')->first();
        $this->put(route('admin.pages.update', $page), ['title' => 'X', 'slug' => 'admin'])->assertSessionHasErrors('slug');
        $this->put(route('admin.pages.update', $page), ['title' => 'X', 'slug' => 'about'])->assertSessionHasErrors('slug');

        $this->delete(route('admin.pages.destroy', Page::bySlug('home')))->assertSessionHasErrors('delete');
        $this->assertNotNull(Page::bySlug('home'));
        $this->delete(route('admin.pages.destroy', $page))->assertRedirect();
        $this->assertNull(Page::where('slug', 'packages-page')->first());
    }

    public function test_system_page_cannot_be_reached_as_a_custom_page(): void
    {
        $this->get('/home')->assertNotFound();
        $this->get('/terms')->assertNotFound();
    }

    public function test_hidden_section_is_not_rendered(): void
    {
        $this->login();
        $home = Page::bySlug('home');
        $faq = $this->section($home, 'faq');
        $this->post(route('admin.pages.sections.toggle', [$home, $faq['id']]));
        $this->get('/')->assertOk()->assertDontSee('Frequently Asked Questions');
    }

    public function test_navigation_items_are_editable_and_shown_in_header_and_footer(): void
    {
        $this->login();
        $this->get(route('admin.navigation.index'))->assertOk()->assertSee('Navigation');

        $this->post(route('admin.navigation.store'), ['menu' => 'footer', 'kind' => 'link', 'label' => 'Group Travel Link', 'url' => '/group-travel', 'is_active' => '1'])->assertRedirect();
        $this->post(route('admin.navigation.store'), ['menu' => 'header', 'kind' => 'link', 'label' => 'Special Header Link', 'url' => 'https://example.com/x', 'is_active' => '1', 'new_tab' => '1'])->assertRedirect();

        $html = $this->get('/')->getContent();
        $this->assertStringContainsString('Group Travel Link', $html);
        $this->assertStringContainsString('Special Header Link', $html);
        $this->assertStringContainsString('https://example.com/x', $html);

        MenuItem::where('label', 'Special Header Link')->update(['is_active' => false]);
        $this->get('/')->assertDontSee('Special Header Link');
    }

    public function test_navigation_rejects_unsafe_links(): void
    {
        $this->login();
        $this->post(route('admin.navigation.store'), ['menu' => 'footer', 'kind' => 'link', 'label' => 'Bad', 'url' => 'javascript:alert(1)'])->assertSessionHasErrors('url');
    }

    public function test_site_wide_text_is_editable_and_blank_hides_it(): void
    {
        $this->login();
        $this->get('/')->assertSee('Get Instant Quote')->assertSee('Start Your Adventure');

        Setting::put('topbar_link_label', 'Talk to us today');
        Setting::put('topbar_button_label', '');
        $html = $this->get('/')->getContent();
        $this->assertStringContainsString('Talk to us today', $html);
        $this->assertStringNotContainsString('Get Instant Quote', $html);

        $this->get(route('admin.settings'))->assertOk()->assertSee('Top bar link text')->assertSee('Talk to us today');
    }

    public function test_editor_can_edit_pages_but_not_navigation(): void
    {
        $this->login('editor');
        $this->get(route('admin.pages.index'))->assertOk();
        $this->get(route('admin.pages.edit', Page::bySlug('about')))->assertOk();
        $this->get(route('admin.navigation.index'))->assertForbidden();
    }

    public function test_faq_page_shows_faq_schema_and_help_box(): void
    {
        $html = $this->get('/faq')->assertOk()->assertSee('Still have a question?')->getContent();
        $this->assertStringContainsString('"@type":"FAQPage"', $html);
        $this->assertSame(1, preg_match_all('/<h1[\s>]/', $html));
    }
}
