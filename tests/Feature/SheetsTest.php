<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Enquiry;
use App\Models\Setting;
use App\Services\Sheets\SheetsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/** Admin > Google Sheets: every enquiry (with its UTM parameters) is sent as one row to a Google Apps Script web app. All HTTP is faked. */
class SheetsTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private const URL = 'https://script.google.com/macros/s/AKfycbxTEST123_abc-def/exec';

    private const KEY = 'secret-key-0123456789abcdef';

    private const TRACKING = ['utm_source' => 'google', 'utm_medium' => 'cpc', 'utm_campaign' => 'spring', 'utm_term' => 'netherlands tours', 'utm_content' => 'ad1', 'gclid' => 'G123', 'gbraid' => 'GB1', 'wbraid' => 'WB1', 'referrer' => 'https://google.com/', 'landing_page' => 'https://site.test/?utm_source=google'];

    private function login(): void
    {
        $this->post('/admin/login', ['email' => 'admin@test.local', 'password' => 'test-password-123']);
    }

    private function connect(): void
    {
        Setting::put('sheets_url', self::URL);
        Setting::put('sheets_secret', Crypt::encryptString(self::KEY));
    }

    private function fakeGoogle(array $answer = ['ok' => true, 'row' => 2], int $status = 200): void
    {
        Http::fake([
            'script.google.com/*' => Http::response($answer, $status),
            'www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true]),
        ]);
    }

    private function basePost(): array
    {
        return ['first_name' => 'Jane', 'last_name' => 'T', 'email' => 'jane@example.com', 'country_code' => 'US', 'phone' => '4155550132', 'consent' => '1', 'g-recaptcha-response' => 'tok', 'page_url' => 'https://site.test/x'];
    }

    private function sentBody(): array
    {
        $req = Http::recorded()->map(fn ($p) => $p[0])->first(fn (Request $r) => str_starts_with($r->url(), 'https://script.google.com/'));
        $this->assertNotNull($req, 'a call to the Apps Script web app was expected');

        return $req->data();
    }

    public function test_requires_login_and_is_administrators_only(): void
    {
        $this->get('/admin/sheets')->assertRedirect(route('admin.login'));

        AdminUser::create(['name' => 'Ed', 'email' => 'ed@test.local', 'password' => 'editor-pass-123', 'role' => 'editor']);
        $this->post('/admin/login', ['email' => 'ed@test.local', 'password' => 'editor-pass-123']);
        $this->get('/admin/sheets')->assertForbidden();
    }

    public function test_page_shows_status_the_script_the_steps_and_the_columns_but_never_the_saved_key(): void
    {
        $this->login();
        $html = $this->get('/admin/sheets')->assertOk()->getContent();
        $this->assertStringContainsString('Off: add the web app URL', $html);
        $this->assertStringContainsString('0 in the sheet', $html);
        $this->assertStringContainsString('Step by step', $html);
        $this->assertStringContainsString('PASTE-YOUR-SECRET-KEY-HERE', $html, 'the script is on the page');
        $this->assertStringContainsString('function doPost', $html);
        $this->assertStringContainsString('UTM Campaign', $html);
        $this->assertStringContainsString('First GCLID', $html);
        $this->assertStringContainsString('Google Sheets', $this->get('/admin')->getContent(), 'in the admin menu');

        $this->connect();
        $html = $this->get('/admin/sheets')->getContent();
        $this->assertStringContainsString('On: every enquiry is added', $html);
        $this->assertStringContainsString('def', substr(self::KEY, -4) === 'cdef' ? 'cdef' : '', 'sanity');
        $this->assertStringNotContainsString(self::KEY, $html, 'the secret key is never shown again');
        $this->assertStringContainsString('cdef', $html, 'only its last 4 characters');
    }

    public function test_saving_validates_the_address_and_key_and_stores_the_key_encrypted(): void
    {
        $this->login();
        $this->put('/admin/sheets', ['web_app_url' => 'http://script.google.com/macros/s/abc/exec', 'secret_key' => self::KEY, 'enabled' => '1'])->assertSessionHasErrors('web_app_url');
        $this->put('/admin/sheets', ['web_app_url' => 'https://evil.example.com/macros/s/abc/exec', 'secret_key' => self::KEY, 'enabled' => '1'])->assertSessionHasErrors('web_app_url');
        $this->put('/admin/sheets', ['web_app_url' => 'https://script.google.com/macros/s/abc/exec?x=1', 'secret_key' => self::KEY, 'enabled' => '1'])->assertSessionHasErrors('web_app_url');
        $this->put('/admin/sheets', ['web_app_url' => self::URL, 'secret_key' => 'short', 'enabled' => '1'])->assertSessionHasErrors('secret_key');
        $this->assertFalse(sheets_enabled());

        $this->put('/admin/sheets', ['web_app_url' => self::URL, 'secret_key' => self::KEY, 'enabled' => '1'])->assertRedirect(route('admin.sheets'))->assertSessionHasNoErrors();
        $stored = Setting::where('key', 'sheets_secret')->value('value');
        $this->assertNotSame(self::KEY, $stored);
        $this->assertStringNotContainsString('secret-key', $stored);
        $this->assertSame(self::KEY, sheets_secret());
        $this->assertTrue(sheets_enabled());

        // a blank key keeps the saved one; unticking pauses; removing turns it off
        $this->put('/admin/sheets', ['web_app_url' => self::URL, 'secret_key' => '', 'enabled' => '1']);
        $this->assertSame(self::KEY, sheets_secret());
        $this->put('/admin/sheets', ['web_app_url' => self::URL, 'secret_key' => '']);
        $this->assertFalse(sheets_enabled(), 'paused');
        $this->put('/admin/sheets', ['web_app_url' => self::URL, 'secret_key' => '', 'enabled' => '1', 'remove_secret' => '1']);
        $this->assertNull(sheets_secret());
        $this->assertFalse(sheets_enabled());
    }

    #[DataProvider('formTypes')]
    public function test_every_form_adds_one_row_with_the_lead_and_all_utm_parameters(string $form): void
    {
        $this->connect();
        $this->fakeGoogle();

        $this->withSession(['tracking_last' => self::TRACKING])
            ->post('/enquiry', ['form_name' => $form, 'budget' => '$2K pp', 'quiz' => ['season' => 'Spring']] + $this->basePost())
            ->assertRedirect(route('thank-you'));

        $body = $this->sentBody();
        $this->assertSame(self::KEY, $body['secret']);
        $this->assertSame(SheetsService::headers(), $body['headers']);
        $row = $body['row'];
        $this->assertSame(array_keys($row), $body['headers'], 'one value for every column, keyed by header');
        $this->assertSame('Jane', $row['First Name']);
        $this->assertSame('jane@example.com', $row['Email']);
        $this->assertSame('+14155550132', $row['Phone']);
        $this->assertSame(config('gnt.form_names.'.$form), $row['Form']);
        $this->assertSame('Yes', $row['Consent Given']);
        $this->assertStringContainsString('Season: Spring', $row['Quiz Answers']);
        $this->assertNotSame('', $row['Received']);
        foreach (['UTM Source' => 'google', 'UTM Medium' => 'cpc', 'UTM Campaign' => 'spring', 'UTM Term' => 'netherlands tours', 'UTM Content' => 'ad1', 'GCLID' => 'G123', 'GBRAID' => 'GB1', 'WBRAID' => 'WB1', 'Referrer' => 'https://google.com/'] as $col => $expected) {
            $this->assertSame($expected, $row[$col], $col.' (last visit)');
            $this->assertSame($expected, $row['First '.$col], 'First '.$col);
        }
        $this->assertArrayNotHasKey('IP Address', $row, 'no IP addresses are written to the sheet');

        $e = Enquiry::first();
        $this->assertSame('synced', $e->sheets_status);
        $this->assertNotNull($e->sheets_synced_at);
        $this->assertSame((string) $e->id, $row['Enquiry ID']);
    }

    public static function formTypes(): array
    {
        return [['hero-quiz'], ['header-modal'], ['package-quote'], ['package-question'], ['contact']];
    }

    public function test_nothing_is_sent_while_off_and_the_enquiry_is_marked_skipped(): void
    {
        Http::fake(['www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true])]);
        $this->assertFalse(sheets_enabled());
        $this->post('/enquiry', ['form_name' => 'contact'] + $this->basePost())->assertRedirect();
        Http::assertNotSent(fn (Request $r) => str_contains($r->url(), 'script.google.com'));
        $this->assertSame('off', Enquiry::first()->sheets_status);
    }

    public function test_a_failing_google_never_blocks_the_enquiry_and_the_error_hides_the_key(): void
    {
        $this->connect();
        Http::fake([
            'script.google.com/*' => Http::response(['ok' => false, 'error' => 'Boom with '.self::KEY], 200),
            'www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true]),
        ]);

        $this->post('/enquiry', ['form_name' => 'contact'] + $this->basePost())->assertRedirect(route('thank-you'));

        $e = Enquiry::first();
        $this->assertSame('failed', $e->sheets_status);
        $this->assertStringContainsString('Boom', $e->sheets_error);
        $this->assertStringNotContainsString(self::KEY, $e->sheets_error);
    }

    public function test_a_sign_in_page_instead_of_json_gives_a_helpful_message(): void
    {
        $this->connect();
        Http::fake(['script.google.com/*' => Http::response('<html>Sign in</html>', 200)]);
        $enquiry = Enquiry::create($this->enquiryAttributes());

        \App\Jobs\SyncEnquiryToSheets::dispatchSync($enquiry->id);

        $this->assertSame('failed', $enquiry->fresh()->sheets_status);
        $this->assertStringContainsString('Who has access: Anyone', $enquiry->fresh()->sheets_error);
    }

    public function test_test_row_button_sends_a_labelled_sample_and_reports_errors(): void
    {
        $this->login();
        $this->post('/admin/sheets/test')->assertSessionHasErrors('sheets');   // nothing saved yet

        $this->connect();
        $this->fakeGoogle();
        $this->post('/admin/sheets/test')->assertRedirect(route('admin.sheets'))->assertSessionHas('status');
        $row = $this->sentBody()['row'];
        $this->assertStringStartsWith('TEST-', $row['Enquiry ID']);
        $this->assertStringContainsString('Test row', $row['Form']);
        $this->assertSame(0, Enquiry::count(), 'a test row is not saved as an enquiry');

        Http::swap(new \Illuminate\Http\Client\Factory());   // a fresh fake: stubs added later would otherwise never be reached
        $this->fakeGoogle(['ok' => false, 'error' => 'The secret key does not match.']);
        $this->post('/admin/sheets/test')->assertSessionHasErrors('sheets');
    }

    public function test_backfill_sends_skipped_and_failed_enquiries_once_and_retry_works_per_enquiry(): void
    {
        $this->login();
        $a = Enquiry::create($this->enquiryAttributes(['email' => 'a@example.com']));
        $b = Enquiry::create($this->enquiryAttributes(['email' => 'b@example.com', 'sheets_status' => 'failed']));
        $c = Enquiry::create($this->enquiryAttributes(['email' => 'c@example.com', 'sheets_status' => 'synced']));

        $this->post('/admin/sheets/backfill')->assertSessionHasErrors('sheets');   // off: connect first

        $this->connect();
        $this->fakeGoogle();
        $this->post('/admin/sheets/backfill')->assertSessionHas('status');
        $this->assertSame('synced', $a->fresh()->sheets_status);
        $this->assertSame('synced', $b->fresh()->sheets_status);
        $sent = Http::recorded()->filter(fn ($p) => str_starts_with($p[0]->url(), 'https://script.google.com/'))->count();
        $this->assertSame(2, $sent, 'the already synced enquiry is not sent again');

        $c->update(['sheets_status' => 'failed']);
        $this->post(route('admin.enquiries.sheets-retry', $c))->assertSessionHas('status');
        $this->assertSame('synced', $c->fresh()->sheets_status);
        $this->get(route('admin.enquiries.show', $c))->assertOk()->assertSee('Google Sheets')->assertSee('Send to the sheet again');
    }

    public function test_only_google_apps_script_addresses_are_ever_called(): void
    {
        Setting::put('sheets_url', 'https://evil.example.com/macros/s/abc/exec');
        Setting::put('sheets_secret', Crypt::encryptString(self::KEY));
        Http::fake();
        $enquiry = Enquiry::create($this->enquiryAttributes());

        \App\Jobs\SyncEnquiryToSheets::dispatchSync($enquiry->id);

        Http::assertNothingSent();
        $this->assertSame('failed', $enquiry->fresh()->sheets_status);
    }

    public function test_the_apps_script_file_is_complete(): void
    {
        $script = file_get_contents(resource_path('sheets/apps-script.gs'));
        foreach (['function doPost', 'body.secret', 'LockService', "'Enquiry ID'", 'setNumberFormat', 'function doGet'] as $needle) {
            $this->assertStringContainsString($needle, $script, $needle);
        }
        $this->assertSame(1, substr_count($script, "var SECRET = 'PASTE-YOUR-SECRET-KEY-HERE';"));
    }

    private function enquiryAttributes(array $over = []): array
    {
        return $over + [
            'form_name' => 'contact', 'first_name' => 'Jane', 'last_name' => 'T', 'email' => 'jane@example.com', 'country_code' => 'US',
            'phone_national' => '4155550132', 'phone_e164' => '+14155550132', 'consent_given' => true, 'consent_text' => 'x', 'terms_version' => '1.0', 'consented_at' => now(),
        ];
    }
}
