<?php

namespace App\Services;

use App\Jobs\SyncEnquiryToGhl;
use App\Mail\NewEnquiryMail;
use App\Models\Enquiry;
use App\Models\Package;
use App\Models\QuizStep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/** Single place where an enquiry is turned into a database row (+ consent/tracking evidence) and queued work. */
class EnquiryService
{
    /** Combine ISO country + national number into E.164, or null when it cannot be valid. */
    public function e164(?string $iso, ?string $phone): ?string
    {
        $dial = collect(config('gnt.countries'))->firstWhere(2, $iso)[1] ?? null;
        $digits = preg_replace('/\D/', '', (string) $phone);
        if (! $dial || $digits === '') {
            return null;
        }
        if (str_starts_with($digits, $dial) && strlen($digits) > strlen($dial) + 6) {
            $digits = substr($digits, strlen($dial));       // user typed the country code too
        }
        $digits = ltrim($digits, '0');
        $total = strlen($dial.$digits);

        return ($digits !== '' && $total >= 8 && $total <= 15) ? '+'.$dial.$digits : null;
    }

    public function create(array $data, Request $request): Enquiry
    {
        $iso = $data['country_code'];
        $dial = collect(config('gnt.countries'))->firstWhere(2, $iso)[1];
        $e164 = $this->e164($iso, $data['phone']);

        $package = ! empty($data['package_id']) ? Package::find($data['package_id']) : null;

        $enquiry = Enquiry::create([
            'form_name' => $data['form_name'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => mb_strtolower($data['email']),
            'country_code' => '+'.$dial,
            'phone_national' => preg_replace('/\D/', '', $data['phone']),
            'phone_e164' => $e164,
            // The quiz's "Preferred travel dates" step (key travel_date) fills the same field as the other forms.
            'travel_date' => ($data['travel_date'] ?? null) ?: ($data['quiz']['travel_date'] ?? null),
            'second_quote' => $data['second_quote'] ?? null,
            'special_request' => $data['special_request'] ?? null,
            'package_id' => $package?->id,
            'package_name' => $package?->title,
            'quiz_answers' => $this->answers($data) ?: null,
            // chargeback / compliance evidence
            'consent_given' => true,
            'consent_text' => consent_text(),
            'terms_version' => terms_version(),
            'consented_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'page_url' => mb_substr((string) ($data['page_url'] ?? $request->headers->get('referer') ?? ''), 0, 500),
            'tracking_first' => $this->firstTouch($request),
            'tracking_last' => $this->lastTouch($request, $data),
            'ghl_status' => ghl_enabled() ? 'pending' : 'off',
        ]);

        $this->dispatchWork($enquiry);

        return $enquiry;
    }

    /** Quiz answers (only keys that exist as quiz steps) plus the optional season/group/nights fields. */
    private function answers(array $data): array
    {
        $allowed = QuizStep::pluck('key')->all();
        $answers = collect($data['quiz'] ?? [])->only(array_merge($allowed, ['destination']))->filter(fn ($v) => $v !== '')->all();
        foreach (['season', 'group_size', 'nights', 'destination', 'travel_style', 'budget'] as $k) {
            if (! empty($data[$k]) && ! isset($answers[$k])) {
                $answers[$k] = $data[$k];
            }
        }

        return $answers;
    }

    private function firstTouch(Request $request): ?array
    {
        $raw = $request->cookie(config('gnt.tracking_cookie'));
        $decoded = is_string($raw) ? json_decode($raw, true) : null;
        if (! is_array($decoded)) {
            return $request->session()->get('tracking_last');
        }
        $keys = array_merge(config('gnt.tracking_keys'), ['referrer', 'landing_page', 'first_seen']);

        return collect($decoded)->only($keys)->map(fn ($v) => mb_substr((string) $v, 0, 500))->all() ?: null;
    }

    private function lastTouch(Request $request, array $data): array
    {
        $last = $request->session()->get('tracking_last', []);
        $first = $this->firstTouch($request) ?? [];

        // If this session carried no tracking params, the latest touch equals the first touch.
        return ($last ?: $first) + [
            'form_name' => $data['form_name'],
            'page_url' => $data['page_url'] ?? null,
        ];
    }

    private function dispatchWork(Enquiry $enquiry): void
    {
        $to = array_filter(array_map('trim', explode(',', (string) setting('notification_emails'))));
        if ($to) {
            try {
                Mail::to($to)->queue(new NewEnquiryMail($enquiry));
            } catch (\Throwable $e) {
                Log::error('Enquiry email failed: '.$e->getMessage());   // never block the enquiry
            }
        }

        if (ghl_enabled()) {
            try {
                SyncEnquiryToGhl::dispatch($enquiry->id);
            } catch (\Throwable $e) {
                Log::error('GHL dispatch failed: '.$e->getMessage());
            }
        }
    }
}
