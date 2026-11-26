<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $days = in_array((int) $request->query('days'), [7, 30, 90], true) ? (int) $request->query('days') : 30;
        $from = now()->subDays($days - 1)->startOfDay();

        $period = Enquiry::where('created_at', '>=', $from)->get(['id', 'form_name', 'package_name', 'status', 'tracking_first', 'tracking_last', 'created_at']);
        $total = $period->count();
        $new = $period->where('status', 'new')->count();

        // Where they came from: the last touch (utm source, else the referrer's host, else "Direct").
        $touch = fn (Enquiry $e) => (array) ($e->tracking_last ?: $e->tracking_first ?: []);
        $sources = $period->groupBy(function (Enquiry $e) use ($touch) {
            $t = $touch($e);
            if (! empty($t['utm_source'])) {
                return (string) $t['utm_source'];
            }
            $host = ! empty($t['referrer']) ? parse_url((string) $t['referrer'], PHP_URL_HOST) : null;

            return $host ?: 'Direct';
        })->map->count()->sortDesc()->take(5);

        $campaigns = $period->groupBy(fn (Enquiry $e) => $touch($e)['utm_campaign'] ?? '')->forget('')->map->count()->sortDesc()->take(5);
        $forms = $period->groupBy(fn (Enquiry $e) => config('gnt.form_names.'.$e->form_name, $e->form_name))->map->count()->sortDesc()->take(5);
        $journeys = $period->filter(fn (Enquiry $e) => filled($e->package_name))->groupBy('package_name')->map->count()->sortDesc()->take(5);
        $ads = $period->filter(function (Enquiry $e) use ($touch) {
            $t = $touch($e);

            return ! empty($t['gclid']) || in_array(strtolower((string) ($t['utm_medium'] ?? '')), ['cpc', 'ppc', 'paid'], true);
        })->count();

        // One bar per day in the period.
        $perDay = $period->groupBy(fn (Enquiry $e) => $e->created_at->toDateString())->map->count();
        $series = collect(range(0, $days - 1))->map(function ($i) use ($from, $perDay) {
            $d = $from->copy()->addDays($i);

            return ['date' => $d, 'n' => (int) ($perDay[$d->toDateString()] ?? 0)];
        });

        $pipeline = collect(Enquiry::STATUSES)->map(fn ($label, $key) => ['label' => $label, 'key' => $key, 'n' => $period->where('status', $key)->count()]);

        $stale = Enquiry::where('status', 'new')->where('created_at', '<', now()->subDay())->count();

        $placeholders = Setting::where('value', 'like', '%[CONFIRM%')->count();
        $health = [
            ['ok' => filled(config('services.recaptcha.site_key')) && filled(config('services.recaptcha.secret_key')), 'title' => 'Real reCAPTCHA keys', 'fix' => 'Add the keys in the server settings (.env).', 'url' => null],
            ['ok' => ghl_enabled(), 'title' => 'GoHighLevel connected', 'fix' => 'Enquiries are not being sent to your CRM.', 'url' => route('admin.ghl')],
            ['ok' => sheets_enabled(), 'title' => 'Google Sheet log connected', 'fix' => 'Enquiries are not being logged to a sheet.', 'url' => route('admin.sheets')],
            ['ok' => filled(setting('phone')), 'title' => 'Phone number set', 'fix' => 'The site shows a placeholder phone number.', 'url' => route('admin.settings')],
            ['ok' => filled(setting('gtm_id')), 'title' => 'Google Tag Manager ID set', 'fix' => 'Conversion events (enquiry_submit, enquiry_thank_you) are not being sent.', 'url' => route('admin.settings')],
            ['ok' => filled(setting('notification_emails')), 'title' => 'Notification email set', 'fix' => 'Nobody is emailed when an enquiry arrives.', 'url' => route('admin.settings')],
            ['ok' => $placeholders === 0, 'title' => 'No [CONFIRM] placeholders in settings', 'fix' => $placeholders.' setting(s) still hold a placeholder (hidden on the public site).', 'url' => route('admin.settings')],
        ];

        return view('admin.dashboard', [
            'days' => $days,
            'total' => $total,
            'new' => $new,
            'newAll' => Enquiry::where('status', 'new')->count(),
            'ads' => $ads,
            'won' => $period->where('status', 'won')->count(),
            'series' => $series,
            'pipeline' => $pipeline,
            'sources' => $sources,
            'campaigns' => $campaigns,
            'forms' => $forms,
            'journeys' => $journeys,
            'stale' => $stale,
            'health' => $health,
            'recent' => Enquiry::latest()->take(8)->get(),
        ]);
    }
}
