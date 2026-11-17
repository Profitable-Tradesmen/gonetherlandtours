<?php

use App\Models\Page;
use App\Models\Setting;

if (! function_exists('setting')) {
    /** Read a site setting (Admin > Settings). Falls back to $default when empty. */
    function setting(string $key, mixed $default = null): mixed
    {
        $value = Setting::all_cached()[$key] ?? null;

        return ($value === null || $value === '') ? $default : $value;
    }
}

if (! function_exists('company_name')) {
    function company_name(): string
    {
        return (string) setting('company_name', 'GoNetherlandTours');
    }
}

if (! function_exists('consent_text')) {
    /** The exact plain-text consent wording stored as evidence with each enquiry. */
    function consent_text(): string
    {
        return str_replace('[Company Name]', company_name(), config('gnt.consent_template'));
    }
}

if (! function_exists('consent_html')) {
    /** Consent wording for display: identical words, with the two legal phrases linked (new tab). */
    function consent_html(): string
    {
        $text = e(consent_text());
        $terms = '<a href="'.e(route('terms')).'" target="_blank" rel="noopener" class="underline">Terms and Conditions</a>';
        $privacy = '<a href="'.e(route('privacy')).'" target="_blank" rel="noopener" class="underline">Privacy Policy</a>';

        return preg_replace(
            ['/Terms and Conditions/', '/Privacy Policy/'],
            [$terms, $privacy],
            $text,
            1
        );
    }
}

if (! function_exists('terms_version')) {
    function terms_version(): string
    {
        static $v;

        return $v ??= (string) (Page::where('slug', 'terms')->value('version') ?? '1.0');
    }
}

if (! function_exists('emphasize')) {
    /** Escape a heading and turn *word* into an italic accent word, e.g. "Planned around *you*". Safe for {!! !!}. */
    function emphasize(?string $text, string $class = 'text-rust'): string
    {
        return preg_replace('/\*(.+?)\*/', '<em class="italic '.e($class).'">$1</em>', e((string) $text));
    }
}

if (! function_exists('clean_html')) {
    /** Allow-list sanitiser for admin-entered rich text (Trix output). */
    function clean_html(?string $html): string
    {
        $allowed = '<p><br><strong><b><em><i><u><ul><ol><li><h2><h3><h4><a><blockquote><div><span>';
        $html = strip_tags((string) $html, $allowed);
        // Drop inline event handlers and javascript: URLs.
        $html = preg_replace('/\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);
        $html = preg_replace('/href\s*=\s*(["\'])\s*javascript:[^"\']*\1/i', 'href="#"', $html);

        return $html;
    }
}

if (! function_exists('recaptcha_site_key')) {
    function recaptcha_site_key(): ?string
    {
        return setting('recaptcha_site_key') ?: config('services.recaptcha.site_key');
    }
}

if (! function_exists('recaptcha_secret_key')) {
    function recaptcha_secret_key(): ?string
    {
        return setting('recaptcha_secret_key') ?: config('services.recaptcha.secret_key');
    }
}

if (! function_exists('ghl_token')) {
    /** GHL Private Integration token: saved in Admin > GoHighLevel (encrypted), else GHL_API_TOKEN in .env. */
    function ghl_token(): ?string
    {
        $saved = setting('ghl_token');
        if ($saved) {
            try {
                return \Illuminate\Support\Facades\Crypt::decryptString($saved);
            } catch (\Throwable) {
                // Unreadable (APP_KEY changed): fall back to .env.
            }
        }

        return config('services.ghl.token') ?: null;
    }
}

if (! function_exists('ghl_location_id')) {
    function ghl_location_id(): ?string
    {
        return setting('ghl_location_id') ?: (config('services.ghl.location_id') ?: null);
    }
}

if (! function_exists('ghl_enabled')) {
    /** GoHighLevel sync is ON only when a token + Location ID exist AND it has not been paused in Admin > GoHighLevel. */
    function ghl_enabled(): bool
    {
        return setting('ghl_enabled', '1') !== '0' && filled(ghl_token()) && filled(ghl_location_id());
    }
}
