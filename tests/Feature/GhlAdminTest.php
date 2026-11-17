<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Setting;
use App\Services\Ghl\GhlFields;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GhlAdminTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function login(): void
    {
        $this->post('/admin/login', ['email' => 'admin@test.local', 'password' => 'test-password-123']);
    }

    private function save(array $extra = []): void
    {
        $this->put('/admin/ghl', ['location_id' => 'LOC1', 'api_token' => 'pit-secret-dec1', 'enabled' => '1'] + $extra)->assertRedirect(route('admin.ghl'));
    }

    public function test_requires_login_and_is_administrators_only(): void
    {
        $this->get('/admin/ghl')->assertRedirect(route('admin.login'));

        AdminUser::create(['name' => 'Ed', 'email' => 'ed@test.local', 'password' => 'editor-pass-123', 'role' => 'editor']);
        $this->post('/admin/login', ['email' => 'ed@test.local', 'password' => 'editor-pass-123']);
        $this->get('/admin/ghl')->assertForbidden();
    }

    public function test_saving_stores_token_encrypted_and_turns_sync_on(): void
    {
        $this->login();
        $this->assertFalse(ghl_enabled());
        $this->save();

        $stored = Setting::where('key', 'ghl_token')->value('value');
        $this->assertNotSame('pit-secret-dec1', $stored);
        $this->assertStringNotContainsString('pit-secret', $stored);
        $this->assertTrue(ghl_enabled());
        $this->assertSame('pit-secret-dec1', ghl_token());
        $this->assertSame('LOC1', ghl_location_id());

        $this->get('/admin/ghl')->assertOk()->assertSee('On: enquiries are sent to GoHighLevel')->assertSee('dec1')->assertDontSee('pit-secret');
    }

    public function test_blank_token_keeps_saved_one_and_untick_pauses(): void
    {
        $this->login();
        $this->save();
        $this->put('/admin/ghl', ['location_id' => 'LOC2', 'api_token' => ''])->assertRedirect();

        $this->assertSame('pit-secret-dec1', ghl_token());
        $this->assertSame('LOC2', ghl_location_id());
        $this->assertFalse(ghl_enabled(), 'unticked = paused');
        $this->get('/admin/ghl')->assertSee('Paused');
    }

    public function test_remove_token_turns_it_off(): void
    {
        $this->login();
        $this->save();
        $this->put('/admin/ghl', ['location_id' => 'LOC1', 'remove_token' => '1', 'enabled' => '1'])->assertRedirect();

        $this->assertNull(ghl_token());
        $this->assertFalse(ghl_enabled());
    }

    public function test_test_connection_reports_missing_fields_and_creates_nothing(): void
    {
        $this->login();
        $this->save();
        Cache::flush();
        Http::fake([
            'services.leadconnectorhq.com/locations/LOC1/customFields*' => Http::response(['customFields' => [['id' => 'a', 'name' => GhlFields::core('travel_date')]]]),
            'services.leadconnectorhq.com/locations/LOC1' => Http::response(['location' => ['name' => 'Acme Tours']]),
            'services.leadconnectorhq.com/contacts/*' => Http::response(['contacts' => []]),
        ]);

        $this->post('/admin/ghl/test')->assertRedirect(route('admin.ghl'))->assertSessionHas('status', fn ($s) => str_contains($s, 'Acme Tours') && str_contains($s, 'missing'));

        Http::assertNotSent(fn (Request $r) => $r->method() !== 'GET');
        Http::assertSent(fn (Request $r) => $r->hasHeader('Authorization', 'Bearer pit-secret-dec1'));
    }

    public function test_missing_optional_location_scope_does_not_fail_the_test(): void
    {
        $this->login();
        $this->save();
        Cache::flush();
        Http::fake([
            'services.leadconnectorhq.com/locations/LOC1/customFields*' => Http::response(['customFields' => []]),
            'services.leadconnectorhq.com/locations/LOC1' => Http::response(['statusCode' => 401, 'message' => 'The token is not authorized for this scope.'], 401),
            'services.leadconnectorhq.com/contacts/*' => Http::response(['contacts' => []]),
        ]);

        $this->post('/admin/ghl/test')->assertSessionHasNoErrors()->assertSessionHas('status', fn ($s) => str_contains($s, 'locations.readonly') && str_contains($s, 'Syncing is not affected'));
    }

    public function test_missing_required_scope_is_named(): void
    {
        $this->login();
        $this->save();
        Cache::flush();
        Http::fake([
            'services.leadconnectorhq.com/locations/LOC1/customFields*' => Http::response(['customFields' => []]),
            'services.leadconnectorhq.com/locations/LOC1' => Http::response(['location' => ['name' => 'A']]),
            'services.leadconnectorhq.com/contacts/*' => Http::response(['statusCode' => 401, 'message' => 'The token is not authorized for this scope.'], 401),
        ]);

        $this->post('/admin/ghl/test')->assertSessionHasErrors('ghl');
        $this->assertStringContainsString('contacts.readonly', session('errors')->first('ghl'));
    }

    public function test_failed_connection_shows_error_without_leaking_token(): void
    {
        $this->login();
        $this->save();
        Http::fake(['services.leadconnectorhq.com/*' => Http::response(['message' => 'Invalid token pit-secret-dec1'], 401)]);

        $res = $this->post('/admin/ghl/test')->assertRedirect(route('admin.ghl'));
        $res->assertSessionHasErrors('ghl');
        $this->assertStringNotContainsString('pit-secret-dec1', session('errors')->first('ghl'));
    }

    public function test_test_without_credentials_does_not_call_ghl(): void
    {
        $this->login();
        Http::fake();
        $this->post('/admin/ghl/test')->assertSessionHasErrors('ghl');
        $this->post('/admin/ghl/setup')->assertSessionHasErrors('ghl');
        Http::assertNothingSent();
    }

    public function test_setup_creates_missing_fields(): void
    {
        $this->login();
        $this->save();
        Cache::flush();
        Http::fake([
            'services.leadconnectorhq.com/locations/LOC1/customFields*' => Http::sequence()->push(['customFields' => []])->whenEmpty(Http::response(['customField' => ['id' => 'n']])),
        ]);

        $this->post('/admin/ghl/setup')->assertSessionHas('status', fn ($s) => str_starts_with($s, count(GhlFields::all()).' custom fields created'));
    }
}
