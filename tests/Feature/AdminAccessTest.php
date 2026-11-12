<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_admin_pages_require_login(): void
    {
        foreach (['/admin', '/admin/enquiries', '/admin/packages', '/admin/settings', '/admin/quiz', '/admin/users', '/admin/media', '/admin/enquiries/export'] as $url) {
            $this->get($url)->assertRedirect(route('admin.login'));
        }
    }

    public function test_admin_post_requires_login(): void
    {
        $this->post('/admin/packages', ['title' => 'x'])->assertRedirect(route('admin.login'));
    }

    public function test_seeded_admin_can_log_in_and_see_dashboard(): void
    {
        $this->post('/admin/login', ['email' => 'admin@test.local', 'password' => 'test-password-123'])->assertRedirect(route('admin.dashboard'));
        $this->get('/admin')->assertOk()->assertSee('Dashboard');
    }

    public function test_wrong_password_is_rejected(): void
    {
        $this->post('/admin/login', ['email' => 'admin@test.local', 'password' => 'nope'])->assertSessionHasErrors('email');
        $this->get('/admin')->assertRedirect(route('admin.login'));
    }

    public function test_login_is_throttled(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/admin/login', ['email' => 'admin@test.local', 'password' => 'wrong']);
        }
        $this->post('/admin/login', ['email' => 'admin@test.local', 'password' => 'test-password-123'])->assertStatus(429);
    }

    public function test_admin_cannot_delete_self_or_last_admin(): void
    {
        $admin = AdminUser::first();
        $this->actingAs($admin, 'admin')->delete(route('admin.users.destroy', $admin->id))->assertSessionHasErrors('delete');
        $this->assertDatabaseCount('admin_users', 1);
    }

    public function test_admin_pages_load_when_logged_in(): void
    {
        $this->actingAs(AdminUser::first(), 'admin');
        foreach (['/admin/enquiries', '/admin/packages', '/admin/packages/create', '/admin/tour-types', '/admin/destinations', '/admin/quiz', '/admin/quiz/1/edit', '/admin/pages', '/admin/pages/1/edit', '/admin/pages/4/edit', '/admin/faqs', '/admin/testimonials', '/admin/media', '/admin/settings', '/admin/users'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_package_can_be_created_published_and_seen_on_site(): void
    {
        $this->actingAs(AdminUser::first(), 'admin')->post('/admin/packages', [
            'title' => 'Test Package', 'nights' => 5, 'currency' => 'EUR', 'price_from' => 1999, 'is_published' => 1,
            'highlights_text' => "One\nTwo",
        ])->assertRedirect();

        $this->get('/packages/test-package')->assertOk()->assertSee('Test Package')->assertSee('€1,999');
    }

    public function test_draft_package_is_not_public(): void
    {
        $this->actingAs(AdminUser::first(), 'admin')->post('/admin/packages', ['title' => 'Draft Pkg', 'nights' => 5, 'currency' => 'EUR']);
        auth('admin')->logout();

        $this->get('/packages/draft-pkg')->assertNotFound();
    }
}
