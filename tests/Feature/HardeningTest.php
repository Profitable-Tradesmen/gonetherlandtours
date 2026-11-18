<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Security headers, safe rich text, error pages, query budget and admin guard rails. */
class HardeningTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function login(): void
    {
        $this->actingAs(AdminUser::create(['name' => 'T', 'email' => 'hard@test.local', 'password' => 'test-password-123', 'role' => 'admin']), 'admin');
    }

    public function test_security_headers_are_sent(): void
    {
        $res = $this->get('/')->assertOk();
        $csp = $res->headers->get('Content-Security-Policy');
        foreach (["frame-ancestors 'self'", "base-uri 'self'", "object-src 'none'", "form-action 'self'"] as $d) {
            $this->assertStringContainsString($d, $csp);
        }
        $res->assertHeader('X-Content-Type-Options', 'nosniff')->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }

    public function test_session_cookie_is_https_only_in_production_by_default(): void
    {
        $config = require base_path('config/session.php');
        $this->assertFalse((bool) $config['secure'], 'testing/local stays usable over http');
        putenv('APP_ENV=production');
        $_ENV['APP_ENV'] = $_SERVER['APP_ENV'] = 'production';
        try {
            $this->assertTrue((bool) (require base_path('config/session.php'))['secure']);
        } finally {
            putenv('APP_ENV=testing');
            $_ENV['APP_ENV'] = $_SERVER['APP_ENV'] = 'testing';
        }
    }

    public function test_rich_text_section_strips_script_and_encoded_javascript_links_on_the_public_page(): void
    {
        $this->login();
        $legal = Page::bySlug('privacy');
        $rt = collect($legal->allSections())->firstWhere('type', 'rich_text');
        $this->put(route('admin.pages.sections.update', [$legal, $rt['id']]), ['data' => ['html' => '<p>Safe text</p><a href="jav&#97;script:alert(1)">bad link</a><script>alert(2)</script><img src=x onerror=alert(3)>', 'show_version' => '1']]);

        $html = $this->get('/privacy-policy')->assertOk()->getContent();
        $this->assertStringContainsString('Safe text', $html);
        $this->assertStringNotContainsString('alert(', $html);
        $this->assertStringNotContainsString('javascript:', strtolower($html));
        $this->assertStringNotContainsString('onerror', $html);
    }

    public function test_friendly_error_pages_render_without_the_database(): void
    {
        $e = new \Symfony\Component\HttpKernel\Exception\HttpException(500);
        $html = view('errors.500', ['exception' => $e])->render();
        $this->assertStringContainsString('Something went wrong', $html);
        $this->assertStringContainsString('noindex', $html);
        $this->assertStringContainsString('Error 500', $html);
        $this->assertStringContainsString('We will be right back', view('errors.503')->render());
    }

    public function test_expired_form_and_rate_limit_pages_are_friendly(): void
    {
        $this->assertStringContainsString('That form timed out', view('errors.419')->render());
        $this->assertStringContainsString('Too many attempts', view('errors.429')->render());
    }

    public function test_public_pages_stay_within_a_query_budget(): void
    {
        foreach (['/' => 30, '/about' => 20, '/packages' => 22, '/packages/sample-tulip-season-self-drive' => 20] as $url => $max) {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->get($url)->assertOk();
            $this->assertLessThanOrEqual($max, count(DB::getQueryLog()), "$url ran too many database queries (a repeated query or a missing eager load?)");
        }
    }

    public function test_photo_saved_without_alt_text_triggers_a_warning(): void
    {
        $this->login();
        $page = Page::bySlug('home');
        $band = collect($page->allSections())->firstWhere('type', 'why_band');

        $this->put(route('admin.pages.sections.update', [$page, $band['id']]), ['data' => ['heading' => 'H', 'image' => 'images/feature_band.webp', 'image_alt' => '']])
            ->assertSessionHas('warning', fn ($w) => str_contains($w, 'alt text') && str_contains($w, 'Photo'));

        $this->put(route('admin.pages.sections.update', [$page, $band['id']]), ['data' => ['heading' => 'H', 'image' => 'images/feature_band.webp', 'image_alt' => 'Windmills at sunrise']])
            ->assertSessionMissing('warning');
    }

    public function test_repeater_photo_without_alt_is_reported(): void
    {
        $missing = \App\Support\Sections::missingAlt('team', ['items' => [['name' => 'A', 'image' => 'images/couples.webp', 'image_alt' => ''], ['name' => 'B', 'image' => '', 'image_alt' => '']]]);
        $this->assertSame(['Member 1 (Photo (optional))'], $missing);
    }
}
