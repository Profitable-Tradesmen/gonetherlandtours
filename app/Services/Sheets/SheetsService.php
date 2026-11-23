<?php

namespace App\Services\Sheets;

use App\Models\Enquiry;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Sends one row per enquiry to a Google Sheet through a small Google Apps Script "web app" that the site owner pastes into their own
 * spreadsheet (see Admin > Google Sheets for the script and the steps). No Google account or API key is stored here: only the web app
 * address and a shared secret key (encrypted) that the script checks, so nobody else can write to the sheet.
 *
 * Every row is keyed by column NAME, not position, so the sheet can be re-ordered or get extra columns of its own without breaking.
 */
class SheetsService
{
    /** Only Google Apps Script web apps are accepted as the destination (the setting cannot be used to call any other address). */
    public const URL_PATTERN = '#^https://script\.google\.com/macros/s/[A-Za-z0-9_-]+/exec$#';

    /** Tracking keys written for both the latest and the first visit. */
    private const TRACKING = ['utm_source' => 'UTM Source', 'utm_medium' => 'UTM Medium', 'utm_campaign' => 'UTM Campaign', 'utm_term' => 'UTM Term', 'utm_content' => 'UTM Content', 'gclid' => 'GCLID', 'gbraid' => 'GBRAID', 'wbraid' => 'WBRAID', 'referrer' => 'Referrer', 'landing_page' => 'Landing Page'];

    public function enabled(): bool
    {
        return sheets_enabled();
    }

    /** All column headers, in the order a brand new sheet gets them. */
    public static function headers(): array
    {
        $core = ['Enquiry ID', 'Received', 'Form', 'First Name', 'Last Name', 'Email', 'Phone', 'Preferred Travel Date', 'Package', 'Second Quote Preference', 'Special Request', 'Quiz Answers', 'Consent Given', 'Consent Time', 'Terms Version', 'Page URL', 'Status'];
        $last = array_values(self::TRACKING);
        $first = array_map(fn ($l) => 'First '.$l, array_values(self::TRACKING));

        return array_merge($core, $last, $first);
    }

    /** Column header => value for one enquiry (latest-visit tracking under the plain names, first visit under "First ..."). */
    public function row(Enquiry $e): array
    {
        $last = (array) ($e->tracking_last ?? []);
        $first = (array) ($e->tracking_first ?? []);
        $answers = collect((array) ($e->quiz_answers ?? []))->map(fn ($v, $k) => ucfirst(str_replace('_', ' ', $k)).': '.(is_array($v) ? implode(', ', $v) : $v))->implode("\n");

        $row = [
            'Enquiry ID' => (string) $e->id,
            'Received' => $e->created_at?->toIso8601String(),
            'Form' => config('gnt.form_names.'.$e->form_name, $e->form_name),
            'First Name' => $e->first_name,
            'Last Name' => $e->last_name,
            'Email' => $e->email,
            'Phone' => $e->phone_e164,
            'Preferred Travel Date' => $e->travel_date,
            'Package' => $e->package_name,
            'Second Quote Preference' => $e->second_quote,
            'Special Request' => $e->special_request,
            'Quiz Answers' => $answers,
            'Consent Given' => $e->consent_given ? 'Yes' : 'No',
            'Consent Time' => $e->consented_at?->toIso8601String(),
            'Terms Version' => $e->terms_version,
            'Page URL' => $e->page_url,
            'Status' => Enquiry::STATUSES[$e->status] ?? $e->status,
        ];
        foreach (self::TRACKING as $key => $label) {
            $row[$label] = $last[$key] ?? ($key === 'landing_page' ? ($e->page_url ?? null) : null);
            $row['First '.$label] = $first[$key] ?? null;
        }

        // one value for every column, in header order (the website and the script both rely on it)
        return array_map(fn ($v) => $v === null ? '' : (string) $v, array_merge(array_fill_keys(self::headers(), ''), $row));
    }

    /** The JSON sent to the script. The secret travels in the body, over HTTPS, and is never logged. */
    public function payload(Enquiry $e): array
    {
        return ['secret' => (string) sheets_secret(), 'headers' => self::headers(), 'row' => $this->row($e)];
    }

    /** POST the row. Throws with a short, secret-free message when the script answers with an error. */
    public function send(Enquiry $e): void
    {
        $this->post($this->payload($e));
    }

    /** A clearly labelled sample row for the "Send a test row" button (it is not saved as an enquiry). */
    public function testPayload(): array
    {
        $row = array_fill_keys(self::headers(), '');
        $row['Enquiry ID'] = 'TEST-'.now()->format('YmdHis');
        $row['Received'] = now()->toIso8601String();
        $row['Form'] = 'Test row from Admin > Google Sheets';
        $row['First Name'] = 'Test';
        $row['Last Name'] = 'Row';
        $row['Email'] = 'test@example.com';
        $row['Special Request'] = 'You can delete this row. It only checks that the connection works.';
        $row['UTM Source'] = 'google';
        $row['UTM Medium'] = 'cpc';
        $row['UTM Campaign'] = 'test-campaign';

        return ['secret' => (string) sheets_secret(), 'headers' => self::headers(), 'row' => $row];
    }

    public function sendTest(): void
    {
        $this->post($this->testPayload());
    }

    private function post(array $payload): void
    {
        $url = (string) sheets_url();
        if (! preg_match(self::URL_PATTERN, $url)) {
            throw new RuntimeException('The web app address is not a Google Apps Script address (https://script.google.com/macros/s/.../exec).');
        }

        // Apps Script answers a POST with a redirect to the real response; Laravel follows it.
        $res = Http::acceptJson()->asJson()->timeout(25)->post($url, $payload);

        if ($res->failed()) {
            throw new RuntimeException('Google answered HTTP '.$res->status().'. '.Str::limit(strip_tags((string) $res->body()), 160));
        }
        $json = $res->json();
        if (! is_array($json)) {
            // A Google sign-in or error page instead of JSON: almost always "Who has access" is not set to Anyone.
            throw new RuntimeException('Google did not return the expected answer. In Apps Script, deploy the web app with "Who has access: Anyone", then paste the new address.');
        }
        if (($json['ok'] ?? false) !== true) {
            throw new RuntimeException(Str::limit((string) ($json['error'] ?? 'The script reported an error.'), 200));
        }
    }
}
