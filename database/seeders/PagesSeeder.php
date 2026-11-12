<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PagesSeeder extends Seeder
{
    public function run(): void
    {
        $company = '[Company Name]'; // replaced at render time by company_name() in the legal views

        $pages = [
            'home' => [
                'title' => 'Home',
                'h1' => 'Tailor-Made Tours of the Netherlands',
                'seo_title' => 'Netherlands Tours | Self Drive, Chauffeur & Coach | GoNetherlandTours',
                'meta_description' => 'Plan your Netherlands trip with local experts. Self drive, chauffeur driven and coach tours for US travellers. Get a free, no-obligation quote in 30 seconds.',
                'content' => [
                    'hero_sub' => 'Canals, tulip fields and charming Dutch towns, planned around you. Answer a few quick questions and get a free quote.',
                    'badges' => ['Local Netherlands experts', 'Tailor-made itineraries', 'Traveller reviews [CONFIRM]'],
                    'why_heading' => 'Why Travellers Choose Us',
                    'why' => [
                        ['title' => 'Local knowledge', 'text' => 'Routes and stops chosen by people who know the Netherlands. [CONFIRM: team background]'],
                        ['title' => 'Tailor-made trips', 'text' => 'Your dates, your pace, your interests. Every itinerary can be adjusted.'],
                        ['title' => 'One simple quote', 'text' => 'Tell us what you want and receive a clear quote with everything listed.'],
                        ['title' => 'Support before and during your trip', 'text' => 'Real people to help with questions. [CONFIRM: support hours]'],
                    ],
                    'tours_heading' => 'Choose How You Want to Travel',
                    'packages_heading' => 'Featured Netherlands Tour Packages',
                    'how_heading' => 'How It Works',
                    'steps' => [
                        ['title' => 'Tell us your plans', 'text' => 'Answer a few quick questions about when, how and who is travelling.'],
                        ['title' => 'Get your tailored quote', 'text' => 'A travel specialist builds a personal itinerary and quote for you.'],
                        ['title' => 'Book and enjoy', 'text' => 'Confirm your trip and head to the Netherlands with everything arranged.'],
                    ],
                    'testimonials_heading' => 'What Travellers Say',
                    'faq_heading' => 'Frequently Asked Questions',
                    'cta_heading' => 'Ready to Plan Your Netherlands Trip?',
                    'cta_text' => 'Get a free, no-obligation quote from a Netherlands travel specialist.',
                ],
            ],
            'about' => [
                'title' => 'About Us', 'h1' => 'About GoNetherlandTours',
                'seo_title' => 'About GoNetherlandTours | Netherlands Tour Specialists',
                'meta_description' => 'Learn about GoNetherlandTours and how we plan tailor-made Netherlands tours.',
                'body' => '<p>[CONFIRM: company story, years in business, team. Sample text only.]</p><p>GoNetherlandTours plans self drive, chauffeur driven and coach tours of the Netherlands for American travellers.</p>',
            ],
            'contact' => [
                'title' => 'Contact', 'h1' => 'Contact Us',
                'seo_title' => 'Contact GoNetherlandTours | Get a Netherlands Tour Quote',
                'meta_description' => 'Contact GoNetherlandTours for a free Netherlands tour quote or to ask a question.',
                'body' => '<p>Send us your details and a travel specialist will get back to you.</p>',
            ],
            'terms' => [
                'title' => 'Terms and Conditions', 'h1' => 'Terms and Conditions', 'version' => '1.0',
                'seo_title' => 'Terms and Conditions | GoNetherlandTours',
                'meta_description' => 'Terms and Conditions for using the GoNetherlandTours website and services.',
                'body' => '<p><strong>STARTER TEMPLATE ONLY. This text has not been reviewed by a lawyer. Have it reviewed before launch.</strong></p>'
                    .'<h2>1. About these terms</h2><p>These terms apply to your use of this website and enquiries made with '.$company.'.</p>'
                    .'<h2>2. Enquiries and quotes</h2><p>Submitting an enquiry does not create a booking. Quotes are subject to availability and may change until confirmed in writing. [CONFIRM: quote validity period]</p>'
                    .'<h2>3. Bookings, payments and cancellations</h2><p>[CONFIRM: deposit, final payment, cancellation and refund terms]</p>'
                    .'<h2>4. SMS messaging</h2><p>If you provide your phone number you consent to receive SMS messages from '.$company.' about your enquiry, booking updates, itinerary updates, reminders and customer support. Message and data rates may apply. Reply STOP to unsubscribe or HELP for assistance. Consent is not a condition of purchase.</p>'
                    .'<h2>5. Travel insurance and responsibilities</h2><p>[CONFIRM: insurance, travel documents and traveller responsibilities]</p>'
                    .'<h2>6. Liability</h2><p>[CONFIRM: limitation of liability wording]</p>'
                    .'<h2>7. Governing law</h2><p>[CONFIRM: governing law and jurisdiction]</p>'
                    .'<h2>8. Contact</h2><p>Questions about these terms: [CONFIRM: contact details].</p>',
            ],
            'privacy' => [
                'title' => 'Privacy Policy', 'h1' => 'Privacy Policy', 'version' => '1.0',
                'seo_title' => 'Privacy Policy | GoNetherlandTours',
                'meta_description' => 'How GoNetherlandTours collects, uses and protects your personal information.',
                'body' => '<p><strong>STARTER TEMPLATE ONLY. This text has not been reviewed by a lawyer. Have it reviewed before launch.</strong></p>'
                    .'<h2>1. Who we are</h2><p>'.$company.' ("we") runs this website. [CONFIRM: registered address and data controller details]</p>'
                    .'<h2>2. Information we collect</h2><p>Name, email, phone number, travel preferences, quiz answers and messages you send us. We also collect technical and marketing data such as IP address, browser, pages visited and advertising identifiers (for example UTM parameters and Google click IDs).</p>'
                    .'<h2>3. How we use it</h2><p>To respond to your enquiry, prepare quotes, contact you by email, phone or SMS (with your consent), improve our website and measure advertising.</p>'
                    .'<h2>4. Who we share it with</h2><p>Service providers that help us run the business, such as our customer relationship management and email systems, analytics and advertising providers. [CONFIRM: full list of processors]</p>'
                    .'<h2>5. Cookies and tracking</h2><p>We use cookies to remember how you found us and to measure our advertising. [CONFIRM: cookie banner and consent approach]</p>'
                    .'<h2>6. Your rights</h2><p>You can ask to access, correct or delete your information, and to stop marketing messages. Reply STOP to any SMS to unsubscribe. [CONFIRM: rights process and contact]</p>'
                    .'<h2>7. Retention and security</h2><p>[CONFIRM: retention periods]. We take reasonable steps to protect your information.</p>',
            ],
        ];

        foreach ($pages as $slug => $data) {
            Page::firstOrCreate(['slug' => $slug], $data);
        }
    }
}
