<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'brand_name' => 'GoNetherlandTours',
            'company_name' => 'GoNetherlandTours',        // legal name used in consent text + legal pages
            'tagline' => 'Self drive, chauffeur and coach tours of the Netherlands',
            'phone' => '',                                 // [CONFIRM: phone]
            'email' => '',                                 // [CONFIRM: email]
            'address' => '',                               // [CONFIRM: address]
            'social_facebook' => '',
            'social_instagram' => '',
            'social_youtube' => '',
            'notification_emails' => '',                   // comma separated staff addresses
            'gtm_id' => '',
            'recaptcha_site_key' => '',                    // blank = fall back to .env
            'recaptcha_secret_key' => '',
            'modal_discount' => '[CONFIRM: discount %]',
            'footer_text' => 'Tailor-made tours of the Netherlands for American travellers.',
            'default_seo_title' => 'GoNetherlandTours | Self Drive, Chauffeur & Coach Tours of the Netherlands',
            'default_meta_description' => 'Plan your Netherlands trip with local experts. Self drive, chauffeur driven and coach tours, tailor-made for you. Get a free quote in minutes.',
            'default_social_image' => '',
            'logo' => '',
            'favicon' => '',
        ];

        foreach ($defaults as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
