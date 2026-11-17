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
        'Top bar and header' => [
            'topbar_link_label' => ['Top bar link text', 'text', 'Blank hides it.'],
            'topbar_link_url' => ['Top bar link address', 'text', 'A page path like /contact, or blank to open the quote pop-up.'],
            'topbar_button_label' => ['Top bar button text', 'text', 'Opens the quote pop-up. Blank hides it.'],
            'header_button_label' => ['Header button text', 'text', 'Blank hides it.'],
            'mega_help_text' => ['Tours menu: help text', 'text', ''],
            'mega_view_all_label' => ['Tours menu: "View all" link text', 'text', 'The tour type name is added after it.'],
            'mega_all_packages_label' => ['Tours menu: all packages button', 'text', ''],
            'mega_quote_label' => ['Tours menu: quote button', 'text', ''],
            'nav_promo_title' => ['Dropdown menu panel: heading', 'text', 'The dark panel shown beside dropdown menu cards.'],
            'nav_promo_text' => ['Dropdown menu panel: text', 'text', ''],
            'nav_promo_button' => ['Dropdown menu panel: button', 'text', ''],
        ],
        'Mobile menu and bar' => [
            'mobile_home_label' => ['Mobile menu: Home link', 'text', ''],
            'mobile_tours_heading' => ['Mobile menu: tours heading', 'text', ''],
            'mobile_destinations_heading' => ['Mobile menu: destinations heading', 'text', ''],
            'mobile_more_heading' => ['Mobile menu: other links heading', 'text', ''],
            'mobile_quote_label' => ['Mobile menu: quote button', 'text', ''],
            'mobile_bar_call_label' => ['Bottom bar on phones: call button', 'text', ''],
            'mobile_bar_quote_label' => ['Bottom bar on phones: quote button', 'text', ''],
        ],
        'Pop-up, quiz, footer and other pages' => [
            'modal_title' => ['Quote pop-up: title', 'text', ''],
            'modal_intro' => ['Quote pop-up: intro', 'text', 'Use {discount} where the discount from "Modal headline discount" below should appear.'],
            'quiz_title' => ['Hero quiz card title', 'text', ''],
            'packages_intro' => ['Packages page: intro text', 'text', ''],
            'thankyou_heading' => ['Thank-you page: heading', 'text', 'The visitor\x27s first name is added after it.'],
            'thankyou_text' => ['Thank-you page: text', 'textarea', ''],
            'thankyou_button' => ['Thank-you page: button', 'text', ''],
            'footer_tours_heading' => ['Footer: tours column heading', 'text', ''],
            'footer_company_heading' => ['Footer: links column heading', 'text', 'Footer links are edited in Admin > Navigation.'],
            'footer_copyright' => ['Footer: text after the copyright year and company', 'text', ''],
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
