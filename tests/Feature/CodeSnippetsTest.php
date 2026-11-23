<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\CodeSnippet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Admin > Header & Footer: custom code snippets, output exactly as entered, only where chosen. */
class CodeSnippetsTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function login(string $role = 'admin'): void
    {
        $this->actingAs(AdminUser::create(['name' => 'T', 'email' => $role.'-sn@test.local', 'password' => 'test-password-123', 'role' => $role]), 'admin');
    }

    private function snippet(array $o = []): CodeSnippet
    {
        return CodeSnippet::create($o + ['name' => 'S', 'code' => '<!--snip-->', 'position' => 'head', 'scope' => 'all', 'is_active' => true, 'sort_order' => 0]);
    }

    public function test_only_administrators_can_open_the_area_with_a_warning(): void
    {
        $this->login('editor');
        $this->get(route('admin.snippets.index'))->assertForbidden();
        $this->post(route('admin.snippets.store'), ['name' => 'x', 'code' => '<b>', 'position' => 'head', 'scope' => 'all'])->assertForbidden();

        $this->login('admin');
        $this->get(route('admin.snippets.index'))->assertOk()->assertSee('can break the layout')->assertSee('Header &amp; Footer', false);
        $this->get(route('admin.snippets.create'))->assertOk()->assertSee('invalid code can break your site', false);
    }

    public function test_a_snippet_can_be_added_edited_toggled_and_deleted(): void
    {
        $this->login();
        $code = '<script>window.x = "<b>&amp;";</script>';
        $this->post(route('admin.snippets.store'), ['name' => 'Pixel', 'code' => $code, 'position' => 'body_end', 'scope' => 'all', 'sort_order' => 5, 'is_active' => '1'])->assertRedirect(route('admin.snippets.index'));
        $s = CodeSnippet::firstOrFail();
        $this->assertSame($code, $s->code, 'stored exactly as entered');
        $this->get(route('admin.snippets.index'))->assertOk()->assertSee('Pixel')->assertSee('Entire website')->assertSee('Body end')->assertSee('Enabled');

        $this->put(route('admin.snippets.update', $s->id), ['name' => 'Pixel 2', 'code' => $code, 'position' => 'head', 'scope' => 'all', 'sort_order' => 1])->assertRedirect();
        $this->assertSame('Pixel 2', $s->fresh()->name);
        $this->assertFalse($s->fresh()->is_active, 'unticked = disabled');

        $this->post(route('admin.snippets.toggle', $s->id));
        $this->assertTrue($s->fresh()->is_active);
        $this->delete(route('admin.snippets.destroy', $s->id));
        $this->assertSame(0, CodeSnippet::count());
    }

    public function test_code_is_output_exactly_in_the_chosen_place(): void
    {
        $this->snippet(['name' => 'h', 'code' => '<meta name="x-head" content="1">', 'position' => 'head']);
        $this->snippet(['name' => 's', 'code' => '<i id="x-start"></i>', 'position' => 'body_start']);
        $this->snippet(['name' => 'e', 'code' => '<script>/* x-end */</script>', 'position' => 'body_end']);

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('<meta name="x-head" content="1">', $html);
        $this->assertLessThan(strpos($html, '</head>'), strpos($html, 'x-head'), 'head code is inside <head>');
        $this->assertGreaterThan(strpos($html, '<body'), strpos($html, 'x-start'));
        $this->assertLessThan(strpos($html, 'Skip to main content'), strpos($html, 'x-start'), 'body-start code comes right after the opening body tag');
        $this->assertGreaterThan(strpos($html, '</main>'), strpos($html, 'x-end'));
        $this->assertLessThan(strpos($html, '</body>'), strpos($html, 'x-end'), 'body-end code comes before </body>');
    }

    public function test_disabled_snippets_are_skipped(): void
    {
        $this->snippet(['code' => '<!--on-->']);
        $this->snippet(['code' => '<!--off-->', 'is_active' => false]);
        $this->get('/')->assertSee('<!--on-->', false)->assertDontSee('<!--off-->', false);
    }

    public function test_scope_only_specific_pages_and_exclusions(): void
    {
        $this->snippet(['code' => '<!--about-only-->', 'scope' => 'only', 'include_paths' => "/about"]);
        $this->snippet(['code' => '<!--site-wide-->', 'exclude_paths' => "/contact\n/blog/*"]);
        $this->snippet(['code' => '<!--home-->', 'scope' => 'only', 'include_paths' => '/']);

        $this->get('/about')->assertSee('<!--about-only-->', false)->assertSee('<!--site-wide-->', false)->assertDontSee('<!--home-->', false);
        $this->get('/')->assertDontSee('<!--about-only-->', false)->assertSee('<!--home-->', false);
        $this->get('/contact')->assertDontSee('<!--site-wide-->', false);
        $this->get('/blog')->assertSee('<!--site-wide-->', false);
        $slug = \App\Models\BlogPost::first()->slug;
        $this->get("/blog/$slug")->assertDontSee('<!--site-wide-->', false);
    }

    public function test_addresses_are_normalised_and_order_is_respected(): void
    {
        $this->assertSame('/about', CodeSnippet::normalizePath('about'));
        $this->assertSame('/about', CodeSnippet::normalizePath('https://x.com/about/?a=1'));
        $this->assertSame('/', CodeSnippet::normalizePath('home'));
        $this->assertSame('/blog/*', CodeSnippet::normalizePath('/blog/*'));

        $this->snippet(['code' => '<!--second-->', 'sort_order' => 20]);
        $this->snippet(['code' => '<!--first-->', 'sort_order' => 10]);
        $html = $this->get('/')->getContent();
        $this->assertLessThan(strpos($html, '<!--second-->'), strpos($html, '<!--first-->'));
    }

    public function test_choosing_specific_pages_needs_at_least_one_page(): void
    {
        $this->login();
        $this->post(route('admin.snippets.store'), ['name' => 'x', 'code' => '<b>', 'position' => 'head', 'scope' => 'only'])->assertSessionHasErrors('include_pages');
        $this->post(route('admin.snippets.store'), ['name' => 'x', 'code' => '<b>', 'position' => 'head', 'scope' => 'only', 'include_pages' => ['/about'], 'include_extra' => 'https://x.com/faq', 'is_active' => '1'])->assertRedirect();
        $this->assertSame("/about\n/faq", CodeSnippet::first()->include_paths);
    }
}
