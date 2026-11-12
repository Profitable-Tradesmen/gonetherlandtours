<?php

namespace Tests\Feature;

use App\Models\Enquiry;
use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EnquiryTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function payload(array $override = []): array
    {
        return array_merge([
            'form_name' => 'contact',
            'first_name' => 'Jane',
            'last_name' => 'Traveller',
            'email' => 'Jane@Example.com',
            'country_code' => 'US',
            'phone' => '(415) 555-0132',
            'travel_date' => 'April 2027',
            'second_quote' => 'Yes, please',
            'special_request' => 'Tulips please',
            'consent' => '1',
            'g-recaptcha-response' => 'test-token',
            'page_url' => 'https://example.test/contact',
        ], $override);
    }

    private function fakeRecaptcha(bool $success = true): void
    {
        Http::fake(['www.google.com/recaptcha/api/siteverify' => Http::response(['success' => $success])]);
    }

    public function test_enquiry_fails_without_consent(): void
    {
        $this->fakeRecaptcha();
        $data = $this->payload();
        unset($data['consent']);

        $this->postJson('/enquiry', $data)->assertStatus(422)->assertJsonValidationErrors('consent');
        $this->assertDatabaseCount('enquiries', 0);
    }

    public function test_enquiry_fails_when_consent_is_not_accepted_value(): void
    {
        $this->fakeRecaptcha();

        $this->postJson('/enquiry', $this->payload(['consent' => '0']))->assertStatus(422)->assertJsonValidationErrors('consent');
    }

    public function test_enquiry_fails_without_recaptcha_token(): void
    {
        $this->fakeRecaptcha();
        $data = $this->payload();
        unset($data['g-recaptcha-response']);

        $this->postJson('/enquiry', $data)->assertStatus(422)->assertJsonValidationErrors('g-recaptcha-response');
        $this->assertDatabaseCount('enquiries', 0);
    }

    public function test_enquiry_fails_when_google_says_not_verified(): void
    {
        $this->fakeRecaptcha(false);

        $this->postJson('/enquiry', $this->payload())->assertStatus(422)->assertJsonValidationErrors('g-recaptcha-response');
        $this->assertDatabaseCount('enquiries', 0);
    }

    public function test_enquiry_fails_closed_when_google_is_unreachable(): void
    {
        Http::fake(fn () => throw new \Illuminate\Http\Client\ConnectionException('timeout'));

        $this->postJson('/enquiry', $this->payload())->assertStatus(422)->assertJsonValidationErrors('g-recaptcha-response');
        $this->assertDatabaseCount('enquiries', 0);
    }

    public function test_honeypot_blocks_bots(): void
    {
        $this->fakeRecaptcha();

        $this->postJson('/enquiry', $this->payload(['company_website' => 'http://spam']))->assertStatus(422);
        $this->assertDatabaseCount('enquiries', 0);
    }

    public function test_valid_enquiry_is_saved_with_consent_evidence(): void
    {
        $this->fakeRecaptcha();
        $package = Package::first();

        $this->withHeaders(['User-Agent' => 'PHPUnit-Test-Agent'])
            ->postJson('/enquiry', $this->payload(['form_name' => 'package-quote', 'package_id' => $package->id, 'season' => 'Spring']))
            ->assertOk()->assertJson(['ok' => true]);

        $e = Enquiry::firstOrFail();
        $this->assertSame('jane@example.com', $e->email);
        $this->assertSame('+14155550132', $e->phone_e164);                 // E.164
        $this->assertSame($package->id, $e->package_id);
        $this->assertSame($package->title, $e->package_name);
        $this->assertSame(['season' => 'Spring'], $e->quiz_answers);
        // consent evidence
        $this->assertTrue($e->consent_given);
        $this->assertSame(consent_text(), $e->consent_text);
        $this->assertStringContainsString('Consent is not a condition of purchase.', $e->consent_text);
        $this->assertStringContainsString('provided by GoNetherlandTours.', $e->consent_text);
        $this->assertSame('1.0', $e->terms_version);
        $this->assertNotNull($e->consented_at);
        $this->assertNotNull($e->ip_address);
        $this->assertSame('PHPUnit-Test-Agent', $e->user_agent);
        $this->assertSame('https://example.test/contact', $e->page_url);
        $this->assertSame('off', $e->ghl_status);                          // GHL blank in phpunit.xml
    }

    public function test_quiz_answers_are_stored_as_json(): void
    {
        $this->fakeRecaptcha();

        $this->postJson('/enquiry', $this->payload(['form_name' => 'hero-quiz', 'quiz' => ['season' => 'Spring', 'budget' => '$2K pp', 'bogus_key' => 'x']]))->assertOk();

        $this->assertSame(['season' => 'Spring', 'budget' => '$2K pp'], Enquiry::first()->quiz_answers);
    }

    public function test_quiz_travel_dates_answer_fills_the_enquiry_travel_date(): void
    {
        $this->fakeRecaptcha();
        $data = $this->payload(['form_name' => 'hero-quiz', 'quiz' => ['travel_date' => 'May 2027', 'group_size' => '2']]);
        unset($data['travel_date'], $data['second_quote']);

        $this->postJson('/enquiry', $data)->assertOk();

        $e = Enquiry::first();
        $this->assertSame('May 2027', $e->travel_date);
        $this->assertSame('2', $e->quiz_answers['group_size']);
    }

    public function test_popup_form_requires_budget_and_stores_trip_details(): void
    {
        $this->fakeRecaptcha();
        $details = ['form_name' => 'header-modal', 'destination' => 'Zeeland & the Delta', 'group_size' => '4', 'travel_style' => 'Self drive', 'nights' => '7'];

        $this->postJson('/enquiry', $this->payload($details))->assertStatus(422)->assertJsonValidationErrors('budget');
        $this->assertDatabaseCount('enquiries', 0);

        $this->postJson('/enquiry', $this->payload($details + ['budget' => '$2K pp']))->assertOk();
        $expected = ['group_size' => '4', 'nights' => '7', 'destination' => 'Zeeland & the Delta', 'travel_style' => 'Self drive', 'budget' => '$2K pp'];
        $this->assertEquals($expected, collect(Enquiry::first()->quiz_answers)->sortKeys()->all() === [] ? [] : collect(Enquiry::first()->quiz_answers)->only(array_keys($expected))->all());
    }

    public function test_popup_and_quiz_forms_show_the_requested_fields_only(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        // pop-up
        foreach (['modal-destination', 'modal-group_size', 'modal-travel_date', 'modal-travel_style', 'modal-nights', 'modal-budget', 'modal-special_request'] as $id) {
            $this->assertStringContainsString('id="'.$id.'"', $html, $id);
        }
        $this->assertStringNotContainsString('id="modal-second_quote"', $html);
        // quiz contact step: contact details + special request only
        $this->assertStringContainsString('id="quiz-special_request"', $html);
        foreach (['quiz-travel_date', 'quiz-second_quote', 'quiz-budget', 'quiz-nights'] as $id) {
            $this->assertStringNotContainsString('id="'.$id.'"', $html, $id);
        }
        // quiz steps in the requested order
        $this->assertLessThan(strpos($html, 'Preferred travel dates'), strpos($html, 'What time of year'));
        $this->assertStringContainsString('How likely are you to come to the Netherlands?', $html);
        $this->assertStringNotContainsString("I'm 100% sure", $html);
    }

    public function test_non_javascript_post_redirects_to_thank_you(): void
    {
        $this->fakeRecaptcha();

        $this->post('/enquiry', $this->payload())->assertRedirect(route('thank-you'));
    }

    public function test_consent_text_is_exact_and_company_name_comes_from_settings(): void
    {
        \App\Models\Setting::put('company_name', 'Acme Tours Ltd');

        $this->assertSame(
            'I agree to the Terms and Conditions and Privacy Policy provided by Acme Tours Ltd. By providing my phone number, I consent to receive SMS messages from Acme Tours Ltd regarding my enquiry, booking updates, itinerary updates, reminders, and customer support. Message and data rates may apply. Reply STOP to unsubscribe or HELP for assistance. Consent is not a condition of purchase.',
            consent_text()
        );
    }

    public function test_every_form_renders_consent_checkbox_and_recaptcha_slot(): void
    {
        foreach (['/', '/contact', '/packages/sample-tulip-season-self-drive'] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertStringContainsString('name="consent"', $html, $url);
            $this->assertStringContainsString('data-sitekey', $html, $url);
            $this->assertStringContainsString('name="company_website"', $html, $url);
            $this->assertStringNotContainsString('name="consent" type="checkbox" value="1" required checked', $html);
        }
    }
}
