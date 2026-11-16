<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRolesTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function login(string $role): AdminUser
    {
        $u = AdminUser::create(['name' => ucfirst($role), 'email' => $role.'-user@test.local', 'password' => 'test-password-123', 'role' => $role]);
        $this->actingAs($u, 'admin');

        return $u;
    }

    public function test_editor_can_open_content_areas(): void
    {
        $this->login('editor');
        foreach (['/admin', '/admin/enquiries', '/admin/packages', '/admin/tour-types', '/admin/destinations', '/admin/pages', '/admin/faqs', '/admin/testimonials', '/admin/media'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_editor_is_blocked_from_restricted_areas(): void
    {
        $this->login('editor');
        foreach (['/admin/settings', '/admin/quiz', '/admin/users', '/admin/users/create'] as $url) {
            $this->get($url)->assertForbidden();
        }
        $this->post('/admin/users', ['name' => 'x', 'email' => 'x@x.com', 'password' => 'long-password-1', 'role' => 'admin'])->assertForbidden();
        $this->put('/admin/settings', [])->assertForbidden();
    }

    public function test_editor_nav_hides_restricted_links(): void
    {
        $this->login('editor');
        $this->get('/admin')->assertOk()->assertDontSee('Admin users')->assertDontSee('>Settings<', false)->assertDontSee('>Quiz<', false)->assertSee('Packages');
    }

    public function test_admin_can_create_an_editor_and_that_editor_is_restricted(): void
    {
        $this->login('admin');
        $this->post('/admin/users', ['name' => 'Ed', 'email' => 'ed@test.local', 'password' => 'long-password-1', 'role' => 'editor'])->assertRedirect(route('admin.users.index'));
        $this->assertSame('editor', AdminUser::where('email', 'ed@test.local')->value('role'));

        auth('admin')->logout();
        $this->post('/admin/login', ['email' => 'ed@test.local', 'password' => 'long-password-1'])->assertRedirect(route('admin.dashboard'));
        $this->get('/admin/users')->assertForbidden();
    }

    public function test_invalid_role_is_rejected(): void
    {
        $this->login('admin');
        $this->post('/admin/users', ['name' => 'Bad', 'email' => 'bad@test.local', 'password' => 'long-password-1', 'role' => 'superuser'])->assertSessionHasErrors('role');
    }

    public function test_admin_cannot_demote_self_or_last_admin(): void
    {
        $me = $this->login('admin');
        AdminUser::where('id', '!=', $me->id)->delete();
        $this->put('/admin/users/'.$me->id, ['name' => $me->name, 'email' => $me->email, 'role' => 'editor'])->assertSessionHasErrors('role');
        $this->assertSame('admin', $me->fresh()->role);
    }
}
