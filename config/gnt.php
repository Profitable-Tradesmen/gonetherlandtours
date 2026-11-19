<?php

/*
 * Brand-level business rules for GoNetherlandTours.
 * Anything an admin can edit lives in the settings table instead (see setting()).
 */
return [
    // Exact consent wording. [Company Name] is replaced with the legal company name from settings.
    // Do NOT reword this without legal review: it is evidence stored with every inquiry.
    'consent_template' => 'I agree to the Terms and Conditions and Privacy Policy provided by [Company Name]. By providing my phone number, I consent to receive SMS messages from [Company Name] regarding my enquiry, booking updates, itinerary updates, reminders, and customer support. Message and data rates may apply. Reply STOP to unsubscribe or HELP for assistance. Consent is not a condition of purchase.',

    // Set SITE_NOINDEX=true on preview/staging sites: noindex meta + X-Robots-Tag + "Disallow: /" robots.txt.
    'site_noindex' => (bool) env('SITE_NOINDEX', false),

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

    // Default wording for site-wide labels. Admin > Settings overrides each one (a saved blank hides it where that makes sense).
    'texts' => [
        'card_quote_label' => 'Get a Quote', 'card_details_label' => 'Details', 'card_more_info_label' => 'More info', 'card_from_label' => 'From', 'card_per_person' => 'pp', 'card_sample_badge' => 'Sample',
        'row_packages_suffix' => 'Packages', 'carousel_prev_label' => 'Previous packages', 'carousel_next_label' => 'Next packages', 'carousel_dash_label' => 'Show package', 'carousel_dash_type_label' => 'Show tour type',
        'topbar_link_label' => '', 'topbar_link_url' => '', 'topbar_button_label' => 'Start Your Adventure',
        'header_button_label' => 'Free Quote',
        'mega_view_all_label' => 'View all', 'mega_help_text' => 'Not sure which suits you? We will plan it around you.',
        'mega_all_packages_label' => 'All Packages', 'mega_quote_label' => 'Get a Free Quote',
        'nav_promo_title' => 'Plan with *peace of mind*', 'nav_promo_text' => 'Learn about our team, read what travelers say and find the answers that make your Netherlands trip smooth and stress-free.', 'nav_promo_eyebrow' => 'Get to know us', 'nav_card_link_label' => 'Learn more', 'mega_featured_label' => 'Featured trips', 'nav_promo_button' => 'Get a Free Quote',
        'mobile_home_label' => 'Home', 'mobile_tours_heading' => 'Tours', 'mobile_destinations_heading' => 'Destinations', 'mobile_more_heading' => 'More', 'mobile_quote_label' => 'Free Quote',
        'mobile_bar_call_label' => 'Call', 'mobile_bar_quote_label' => 'Get a Free Quote',
        'footer_tours_heading' => 'Tours', 'footer_destinations_heading' => 'Destinations', 'footer_company_heading' => 'Company', 'footer_copyright' => 'All rights reserved.',
        'modal_title' => 'Start your adventure', 'modal_intro' => 'Fill out this form, save up to {discount} on your trip.',
        'quiz_title' => 'Start Your Adventure',
        'usp_heading' => 'Why travel with us',
        'usp_1_title' => 'Authentic Dutch culture', 'usp_1_text' => 'Windmills, tulip fields, cheese markets and local traditions.',
        'usp_2_title' => 'Small-group & private tours', 'usp_2_text' => 'Personal attention and flexible itineraries, with your own party or a small group.',
        'usp_3_title' => 'Hidden gems & local secrets', 'usp_3_text' => 'Lesser-known places beyond the tourist hotspots.',
        'usp_4_title' => 'Expertly crafted itineraries', 'usp_4_text' => 'Thoughtfully designed routes and carefully selected attractions.',
        'destinations_heading' => 'Destinations', 'destinations_intro' => 'Regions worth slowing down for: pick one and see the tours that visit it.',
        'packages_intro' => 'Browse our Netherlands tour packages and request a free, tailored quote.',
        'thankyou_heading' => 'Thank you', 'thankyou_text' => 'We have received your inquiry. A Netherlands travel specialist will be in touch soon with your tailored quote.', 'thankyou_button' => 'Browse tour packages',
    ],
];
