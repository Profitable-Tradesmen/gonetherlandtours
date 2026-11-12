<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /** Text settings grouped for the form: group => [key => [label, type, help]] */
    public const GROUPS = [
        'Brand and legal' => [
            'brand_name' => ['Brand name', 'text', 'Shown in the header, titles and emails.'],
            'company_name' => ['Legal company name', 'text', 'Used in the consent checkbox text and the Terms and Privacy pages. Changing it changes the consent wording.'],
            'tagline' => ['Tagline', 'text', ''],
            'footer_text' => ['Footer text', 'textarea', ''],
        ],
        'Contact details' => [
            'phone' => ['Phone (click-to-call)', 'text', 'Leave blank to hide it everywhere.'],
            'email' => ['Public email', 'text', ''],
            'address' => ['Address', 'textarea', ''],
            'social_facebook' => ['Facebook URL', 'text', ''],
            'social_instagram' => ['Instagram URL', 'text', ''],
            'social_youtube' => ['YouTube URL', 'text', ''],
        ],
        'Enquiries and tracking' => [
            'notification_emails' => ['Staff notification emails', 'text', 'Comma separated. New enquiries are emailed to these addresses.'],
            'modal_discount' => ['Modal headline discount', 'text', 'Used in "save up to X% on your trip". Only enter a discount you can honour.'],
            'gtm_id' => ['Google Tag Manager ID', 'text', 'For example GTM-XXXXXXX. Blank = GTM is not loaded.'],
            'recaptcha_site_key' => ['reCAPTCHA v2 site key', 'text', 'Blank uses the value in .env (Google test keys locally).'],
            'recaptcha_secret_key' => ['reCAPTCHA v2 secret key', 'secret', 'Leave blank to keep the current secret.'],
        ],
        'Default SEO' => [
            'default_seo_title' => ['Default SEO title', 'text', ''],
            'default_meta_description' => ['Default meta description', 'textarea', ''],
        ],
    ];

    public const FILES = [
        'logo' => 'Logo (PNG, JPG or WebP)',
        'favicon' => 'Favicon (PNG)',
        'default_social_image' => 'Default social sharing image (1200x630 recommended)',
    ];

    public function edit()
    {
        return view('admin.settings', ['groups' => self::GROUPS, 'files' => self::FILES]);
    }

    public function update(Request $request)
    {
        $rules = ['logo_file' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048', 'favicon_file' => 'nullable|image|mimes:png|max:512', 'default_social_image_file' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:4096'];
        foreach (self::GROUPS as $fields) {
            foreach ($fields as $key => [$label, $type]) {
                $rules[$key] = 'nullable|string|max:'.($type === 'textarea' ? 1000 : 255);
            }
        }
        $rules['gtm_id'] = ['nullable', 'regex:/^GTM-[A-Z0-9]+$/'];
        $rules['notification_emails'] = ['nullable', 'string', 'max:500', function ($attr, $value, $fail) {
            foreach (array_filter(array_map('trim', explode(',', (string) $value))) as $mail) {
                if (! filter_var($mail, FILTER_VALIDATE_EMAIL)) {
                    $fail("\"$mail\" is not a valid email address.");
                }
            }
        }];
        $data = $request->validate($rules);

        foreach (self::GROUPS as $fields) {
            foreach ($fields as $key => [$label, $type]) {
                if ($type === 'secret' && blank($data[$key] ?? null)) {
                    continue; // keep the existing secret
                }
                Setting::put($key, trim(strip_tags((string) ($data[$key] ?? ''))));
            }
        }
        foreach (array_keys(self::FILES) as $key) {
            if ($request->hasFile($key.'_file')) {
                $path = $request->file($key.'_file')->store('branding', 'public');
                Setting::put($key, 'storage/'.$path);
            }
        }

        return back()->with('status', 'Settings saved.');
    }
}
