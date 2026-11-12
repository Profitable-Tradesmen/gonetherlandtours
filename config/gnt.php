<?php

/*
 * Brand-level business rules for GoNetherlandTours.
 * Anything an admin can edit lives in the settings table instead (see setting()).
 */
return [
    // Exact consent wording. [Company Name] is replaced with the legal company name from settings.
    // Do NOT reword this without legal review: it is evidence stored with every enquiry.
    'consent_template' => 'I agree to the Terms and Conditions and Privacy Policy provided by [Company Name]. By providing my phone number, I consent to receive SMS messages from [Company Name] regarding my enquiry, booking updates, itinerary updates, reminders, and customer support. Message and data rates may apply. Reply STOP to unsubscribe or HELP for assistance. Consent is not a condition of purchase.',

    'tracking_cookie' => 'gnt_first_touch',
    'tracking_days' => 30,
    'tracking_keys' => ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'gbraid', 'wbraid'],

    'enquiry_rate_limit' => 6, // submissions per minute per IP

    'form_names' => [
        'hero-quiz' => 'Hero Quiz',
        'header-modal' => 'Header Modal',
        'package-quote' => 'Package Quote',
        'package-question' => 'Package Question',
        'contact' => 'Contact Page',
        'tour-type-quote' => 'Tour Type Quote',
        'tour-type-question' => 'Tour Type Question',
    ],

    // [name, dial code, ISO] used by the country code select. First entry is the default.
    'countries' => [
        ['United States', '1', 'US'], ['Canada', '1', 'CA'], ['United Kingdom', '44', 'GB'],
        ['Ireland', '353', 'IE'], ['Australia', '61', 'AU'], ['New Zealand', '64', 'NZ'],
        ['Netherlands', '31', 'NL'], ['Belgium', '32', 'BE'], ['Germany', '49', 'DE'],
        ['France', '33', 'FR'], ['Spain', '34', 'ES'], ['Italy', '39', 'IT'],
        ['Switzerland', '41', 'CH'], ['Austria', '43', 'AT'], ['Sweden', '46', 'SE'],
        ['Norway', '47', 'NO'], ['Denmark', '45', 'DK'], ['Finland', '358', 'FI'],
        ['Portugal', '351', 'PT'], ['Poland', '48', 'PL'], ['Mexico', '52', 'MX'],
        ['Brazil', '55', 'BR'], ['Argentina', '54', 'AR'], ['Chile', '56', 'CL'],
        ['South Africa', '27', 'ZA'], ['India', '91', 'IN'], ['Singapore', '65', 'SG'],
        ['Hong Kong', '852', 'HK'], ['Japan', '81', 'JP'], ['South Korea', '82', 'KR'],
        ['United Arab Emirates', '971', 'AE'], ['Israel', '972', 'IL'], ['Philippines', '63', 'PH'],
    ],

    'second_quote_options' => [
        'Yes, please' => 'Yes, please',
        'No, thank you' => 'No, thank you',
    ],
];
