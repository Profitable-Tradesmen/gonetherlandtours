<?php

namespace Tests\Feature;

use App\Models\Enquiry;
use App\Services\Ghl\GhlFields;
use App\Services\Ghl\GhlService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** GHL is force-blanked in phpunit.xml. These tests enable it with a FAKE token and fake every HTTP call. */
class GhlSyncTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private const TRACKING = ['utm_source' => 'google', 'utm_medium' => 'cpc', 'utm_campaign' => 'spring', 'utm_term' => 'netherlands tours', 'utm_content' => 'ad1', 'gclid' => 'G123', 'gbraid' => 'GB1', 'wbraid' => 'WB1', 'referrer' => 'https://google.com/', 'landing_page' => 'https://site.test/?utm_source=google'];

    private function enableGhl(array $existingContact = []): void
    {
        config(['services.ghl.token' => 'fake-token', 'services.ghl.location_id' => 'LOC1']);
        Cache::flush();
        $fields = collect(GhlFields::all())->map(fn ($n) => ['id' => 'id_'.md5($n), 'name' => $n])->all();

        Http::fake([
            'services.leadconnectorhq.com/locations/LOC1/customFields*' => Http::response(['customFields' => $fields]),
            'services.leadconnectorhq.com/contacts/search/duplicate*' => $existingContact ? Http::response(['contact' => ['id' => 'existing1']]) : Http::response(['message' => 'none'], 404),
            'services.leadconnectorhq.com/contacts/existing1' => Http::response(['contact' => ['customFields' => $existingContact]]),
            'services.leadconnectorhq.com/contacts/upsert' => Http::response(['contact' => ['id' => 'contact1'], 'new' => true]),
            'www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true]),
        ]);
    }

    private function fieldId(string $name): string
    {
        return 'id_'.md5($name);
    }

    private function upsertBody(): array
    {
        $req = Http::recorded()->map(fn ($p) => $p[0])->first(fn (Request $r) => str_ends_with($r->url(), '/contacts/upsert'));
        $this->assertNotNull($req, 'upsert call expected');
        $this->assertSame('Bearer fake-token', $req->header('Authorization')[0]);

        return $req->data();
    }

    private function valueFor(array $body, string $name): ?string
    {
        return collect($body['customFields'])->firstWhere('id', $this->fieldId($name))['field_value'] ?? null;
    }

    public function test_ghl_is_off_when_token_is_blank(): void
    {
        Http::fake(['www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true])]);
        $this->assertFalse(ghl_enabled());
        $this->post('/enquiry', ['form_name' => 'contact'] + $this->basePost())->assertRedirect();
        Http::assertNotSent(fn (Request $r) => str_contains($r->url(), 'leadconnectorhq'));
        $this->assertSame('off', Enquiry::first()->ghl_status);
    }

    private function basePost(): array
    {
        return ['first_name' => 'Jane', 'last_name' => 'T', 'email' => 'jane@example.com', 'country_code' => 'US', 'phone' => '4155550132', 'consent' => '1', 'g-recaptcha-response' => 'tok', 'page_url' => 'https://site.test/x'];
    }

    /** @dataProvider formTypes */
    public function test_payload_includes_utm_and_tracking_fields_for_every_form_type(string $form): void
    {
        $this->enableGhl();

        $this->withSession(['tracking_last' => self::TRACKING])
            ->post('/enquiry', ['form_name' => $form, 'quiz' => ['region' => 'Amsterdam & cities']] + $this->basePost())
            ->assertRedirect(route('thank-you'));

        $body = $this->upsertBody();
        $this->assertSame('jane@example.com', $body['email']);
        $this->assertSame('+14155550132', $body['phone']);
        $this->assertSame('LOC1', $body['locationId']);
        $this->assertContains(config('gnt.form_names.'.$form), $body['tags']);

        foreach (self::TRACKING as $key => $expected) {
            $this->assertSame($expected, $this->valueFor($body, GhlFields::tracking($key, 'last')), "last $key for $form");
            $this->assertSame($expected, $this->valueFor($body, GhlFields::tracking($key, 'first')), "first $key for $form");
        }
        $this->assertSame(config('gnt.form_names.'.$form), $this->valueFor($body, GhlFields::core('form_name')));
        $this->assertSame('Yes', $this->valueFor($body, GhlFields::core('consent_given')));
        $this->assertSame('1.0', $this->valueFor($body, GhlFields::core('consent_version')));
        $this->assertNotNull($this->valueFor($body, GhlFields::core('consent_at')));
        $this->assertSame('Amsterdam & cities', $this->valueFor($body, GhlFields::quiz('region')));
        $this->assertStringContainsString('Region: Amsterdam & cities', $this->valueFor($body, GhlFields::core('quiz_answers')));

        $e = Enquiry::first();
        $this->assertSame('synced', $e->ghl_status);
        $this->assertSame('contact1', $e->ghl_contact_id);
    }

    public static function formTypes(): array
    {
        return [['hero-quiz'], ['header-modal'], ['package-quote'], ['package-question'], ['contact']];
    }

    public function test_returning_contact_keeps_first_touch_and_updates_latest_touch(): void
    {
        $this->enableGhl([['id' => $this->fieldId(GhlFields::tracking('utm_source', 'first')), 'value' => 'facebook']]);

        $this->withSession(['tracking_last' => self::TRACKING])->post('/enquiry', ['form_name' => 'contact'] + $this->basePost());

        $body = $this->upsertBody();
        $this->assertNull($this->valueFor($body, GhlFields::tracking('utm_source', 'first')), 'first-touch must not be overwritten');
        $this->assertSame('google', $this->valueFor($body, GhlFields::tracking('utm_source', 'last')));
    }

    public function test_failed_sync_never_blocks_the_enquiry_and_hides_the_token(): void
    {
        config(['services.ghl.token' => 'super-secret-pit', 'services.ghl.location_id' => 'LOC1']);
        Http::fake([
            'services.leadconnectorhq.com/*' => Http::response(['message' => 'boom'], 500),
            'www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true]),
        ]);

        $this->post('/enquiry', ['form_name' => 'contact'] + $this->basePost())->assertRedirect(route('thank-you'));

        $e = Enquiry::first();
        $this->assertSame('failed', $e->ghl_status);
        $this->assertNotNull($e->ghl_error);
        $this->assertStringNotContainsString('super-secret-pit', $e->ghl_error);
    }

    public function test_setup_creates_only_missing_fields(): void
    {
        config(['services.ghl.token' => 'fake-token', 'services.ghl.location_id' => 'LOC1']);
        Cache::flush();
        $existing = [['id' => 'a', 'name' => GhlFields::core('travel_date')], ['id' => 'b', 'name' => 'UTM Source (First)']];
        Http::fake([
            'services.leadconnectorhq.com/locations/LOC1/customFields*' => Http::sequence()->push(['customFields' => $existing])->whenEmpty(Http::response(['customField' => ['id' => 'new']])),
        ]);

        $result = app(GhlService::class)->setupFields();

        $this->assertNotContains(GhlFields::core('travel_date'), $result['created']);
        $this->assertNotContains('UTM Source (First)', $result['created']);
        $this->assertContains('UTM Source (Last)', $result['created']);
        $this->assertContains('Quiz: Region', $result['created']);
        $this->assertCount(count(GhlFields::all()) - 2, $result['created']);
    }
}
