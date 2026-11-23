<?php

namespace App\Support;

use App\Models\Page;
use App\Models\TourType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Content of the tour type landing pages (/tours/self-drive, /tours/chauffeur-driven, /tours/coach-tours).
 * Each tour type has a companion page record (slug "tour-<tour type slug>", a system page) whose sections are edited in Admin > Pages like any other
 * page. The hero (heading, summary, travel-style note) still comes from Admin > Tour types; everything below it comes from these sections.
 */
class TourTypePages
{
    public static function slugFor(TourType $t): string
    {
        return 'tour-'.$t->slug;
    }

    /** Creates the companion page of every tour type that has none. Never touches a page that exists. Returns how many were created. */
    public static function ensureAll(): int
    {
        $created = 0;
        foreach (TourType::orderBy('sort_order')->get() as $t) {
            if (DB::table('pages')->where('slug', self::slugFor($t))->exists()) {
                continue;
            }
            $now = now();
            DB::table('pages')->insert([
                'slug' => self::slugFor($t), 'title' => $t->name.' page', 'h1' => $t->name,
                'seo_title' => null, 'meta_description' => null,
                'sections' => json_encode(self::sections($t), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'is_published' => true, 'is_system' => true, 'noindex' => true,   // the public page is /tours/<slug>; this record only holds the sections
                'created_at' => $now, 'updated_at' => $now,
            ]);
            $created++;
            self::freshenHero($t);
        }

        return $created;
    }

    /** Replaces the seeded hero heading ("Self Drive Netherlands Tours") with the new one, only while it is still the seeded text. */
    private static function freshenHero(TourType $t): void
    {
        $copy = self::copy($t);
        if ($t->h1 === $t->name.' Netherlands Tours' || blank($t->h1)) {
            $t->update(['h1' => $copy['h1']]);
        }
        if (str_contains((string) $t->body, '[CONFIRM: '.$t->name.' page copy')) {
            $t->update(['body' => null]);   // the placeholder paragraph is replaced by the sections below
        }
    }

    private static function s(string $type, array $data): array
    {
        return ['id' => Str::lower(Str::random(8)), 'type' => $type, 'visible' => true, 'data' => $data + Sections::defaults($type)];
    }

    private static function rows(array $rows, array $keys): array
    {
        return array_map(fn ($r) => array_combine($keys, array_pad(array_values($r), count($keys), '')), $rows);
    }

    /** Copy per tour type (US English). Unknown tour types get a neutral version built from their name. */
    public static function copy(TourType $t): array
    {
        $name = $t->name;
        $lower = Str::lower($name);

        return match ($t->slug) {
            'self-drive' => [
                'h1' => 'The Ultimate Netherlands Self Drive Experience',
                'strip' => [
                    ['shield', 'Planned with care', 'Routes, hotels and driving distances worked out for you before you go.'],
                    ['phone', 'Real support', 'A real person to help before you leave and while you travel. Support is available seven days a week.'],
                    ['money', 'Clear, upfront quotes', 'Know what is included before you decide.'],
                ],
                'eyebrow' => 'Discover the Netherlands by road',
                'heading' => 'Self drive vacations with *real support*',
                'points' => [
                    ['Freedom to stop and linger', 'Pull over at a windmill, a cheese market or a quiet canal town whenever something catches your eye.'],
                    ['Hotels reserved in advance', 'Your accommodation is booked for you, so every day ends somewhere comfortable.'],
                    ['Detailed itineraries with maps', 'Day by day routes with driving times and suggested stops, plus tips for drivers used to North American roads, so you are never guessing.'],
                ],
                'button' => 'Start planning your self drive tour',
                'image' => 'images/tour-self-drive.webp', 'image_alt' => 'A small car on a country road through Dutch polders and tulip fields',
                'pk_eyebrow' => 'Your Dutch driving adventure', 'pk_heading' => 'Self drive packages',
                'pk_intro' => 'Handcrafted itineraries you can follow at your own pace. Every route can be adjusted to your dates and interests.',
                'promo_heading' => 'Ready to plan your perfect *self drive tour?*',
                'promo_text' => 'Every great Netherlands trip starts with a conversation. Tell us what you have in mind and we will build the route around you.',
                'promo_points' => ['Netherlands travel specialists', 'Fully customizable routes', 'Support before and during your trip'],
                'promo_button' => 'Request a custom tour',
                'cta_heading' => 'Ready to start your self drive trip?',
                'cta_text' => 'Get in touch for a self drive tour designed around you, with a clear, no-obligation quote.',
                'step_heading' => '3 easy steps to book your Netherlands self drive tour',
            ],
            'chauffeur-driven' => [
                'h1' => 'Private Chauffeur Driven Netherlands Tours',
                'strip' => [
                    ['heart', 'Comfort first', 'Relax in the back seat while someone else handles the roads.'],
                    ['guide', 'A local driver-guide', 'Someone who knows the country and shares the stories behind each stop.'],
                    ['lock', 'Private and personal', 'Just your own party, on your own timing.'],
                ],
                'eyebrow' => 'Sit back and enjoy the journey',
                'heading' => 'Chauffeur driven tours with *a personal touch*',
                'points' => [
                    ['Your own driver-guide', 'A local driver-guide for your party only, who adjusts the day to what you enjoy most.'],
                    ['Hotels and routes arranged', 'Accommodation, routes and timings are planned for you, with room to explore along the way.'],
                    ['Door-to-door comfort', 'No parking, no navigation, no unfamiliar roads and no stress after the long flight, just time to enjoy the view.'],
                ],
                'button' => 'Plan my chauffeur tour',
                'image' => 'images/tour-chauffeur.webp', 'image_alt' => 'A chauffeur holding the door of a black van on a cobbled Dutch town square',
                'pk_eyebrow' => 'Enjoy comfort and ease', 'pk_heading' => 'Chauffeur driven packages',
                'pk_intro' => 'Private tours with a local driver-guide. Every itinerary can be shaped around your dates, pace and interests.',
                'promo_heading' => 'Ready for a *private chauffeur driven tour?*',
                'promo_text' => 'Tell us who is traveling and what you would love to see. A travel specialist will reply with a clear, personal quote.',
                'promo_points' => ['Private tours for your own party', 'Fully customizable itineraries', 'Support before and during your trip'],
                'promo_button' => 'Request a custom tour',
                'cta_heading' => 'Ready to start your chauffeur driven trip?',
                'cta_text' => 'Get in touch for a private tour designed around you, with a clear, no-obligation quote.',
                'step_heading' => '3 easy steps to book your Netherlands chauffeur driven tour',
            ],
            'coach-tours' => [
                'h1' => 'Small Group Netherlands Coach Tours',
                'strip' => [
                    ['guide', 'Led by a local guide', 'Someone who knows the stories and the shortcuts, and who knows your name.'],
                    ['coach', 'Everything taken care of', 'Transport, sightseeing and accommodation arranged for you.'],
                    ['heart', 'Easy company', 'Meet fellow travelers and share the discoveries along the way.'],
                ],
                'eyebrow' => 'Explore together, effortlessly',
                'heading' => 'Guided coach tours for *small groups*',
                'points' => [
                    ['A small, guided group', 'Travel with a local guide in a small group of up to 12 travelers.'],
                    ['Sightseeing and transport included', 'Comfortable coach travel and the best sights of each region, all organized for you.'],
                    ['Accommodation taken care of', 'Hotels are booked in advance, so you can simply arrive and enjoy.'],
                ],
                'button' => 'Plan my coach tour',
                'image' => 'images/tour-coach.webp', 'image_alt' => 'A small tour minibus parked beside a canal in a charming Dutch village',
                'pk_eyebrow' => 'Expertly guided journeys', 'pk_heading' => 'Coach tour packages',
                'pk_intro' => 'Guided small-group tours with the planning done for you. Ask us about dates and group departures.',
                'promo_heading' => 'Ready to join a *guided coach tour?*',
                'promo_text' => 'Tell us when you would like to travel and we will suggest the departures and itineraries that fit.',
                'promo_points' => ['Small guided groups', 'Transport and sightseeing included', 'Support before and during your trip'],
                'promo_button' => 'Request a quote',
                'cta_heading' => 'Ready to start your coach tour?',
                'cta_text' => 'Get in touch to find the right guided tour for you, with a clear, no-obligation quote.',
                'step_heading' => '3 easy steps to book your Netherlands coach tour',
            ],
            default => [
                'h1' => $name.' Netherlands Tours',
                'strip' => [
                    ['map', 'Planned with care', 'Routes and hotels worked out for you before you go.'],
                    ['phone', 'Real support', 'A real person to help before and during your trip.'],
                    ['money', 'Clear, upfront quotes', 'Know what is included before you decide.'],
                ],
                'eyebrow' => 'Discover the Netherlands',
                'heading' => $name.' tours with *real support*',
                'points' => [
                    ['Planned around you', 'Itineraries shaped by your dates, pace and interests.'],
                    ['Hotels arranged', 'Accommodation is booked in advance.'],
                    ['Clear itineraries', 'Day by day plans so you always know what comes next.'],
                ],
                'button' => 'Start planning my trip',
                'image' => $t->image ?: 'images/Intro_image_split.webp', 'image_alt' => $t->image_alt ?: '',
                'pk_eyebrow' => 'Signature journeys', 'pk_heading' => $name.' packages',
                'pk_intro' => 'Handcrafted itineraries you can adjust to your dates and interests.',
                'promo_heading' => 'Ready to plan your *'.$lower.' trip?*',
                'promo_text' => 'Every great Netherlands trip starts with a conversation. Tell us what you have in mind.',
                'promo_points' => ['Netherlands travel specialists', 'Fully customizable itineraries', 'Support before and during your trip'],
                'promo_button' => 'Request a custom tour',
                'cta_heading' => 'Ready to start your trip?',
                'cta_text' => 'Get in touch for a tour designed around you, with a clear, no-obligation quote.',
                'step_heading' => '3 easy steps to book your Netherlands tour',
            ],
        };
    }

    /** The ordered sections of one tour type page. */
    public static function sections(TourType $t): array
    {
        $c = self::copy($t);

        return [
            self::s('value_strip', ['items' => self::rows($c['strip'], ['icon', 'title', 'text'])]),
            self::s('split_list', [
                'image1' => $c['image'], 'image1_alt' => $c['image_alt'],
                'image2' => 'images/Intro_image_split1.webp', 'image2_alt' => 'A traditional windmill beside rows of tulips at golden hour',
                'badge' => 'Free, no-obligation quote', 'eyebrow' => $c['eyebrow'], 'heading' => $c['heading'],
                'items' => self::rows($c['points'], ['title', 'text']), 'button_label' => $c['button'],
            ]),
            self::s('tour_packages', ['eyebrow' => $c['pk_eyebrow'], 'heading' => $c['pk_heading'], 'intro' => $c['pk_intro']]),
            self::s('why_us', []),
            self::s('promo_band', ['eyebrow' => 'Crafted for you', 'heading' => $c['promo_heading'], 'text' => $c['promo_text'], 'points' => array_map(fn ($p) => ['text' => $p], $c['promo_points']), 'button_label' => $c['promo_button']]),
            self::s('testimonials', ['variant' => 'dark', 'eyebrow' => 'Reviews', 'heading' => 'What our guests say', 'limit' => 3]),
            self::s('steps', ['style' => 'timeline', 'eyebrow' => 'Simple', 'heading' => $c['step_heading'], 'items' => self::rows([
                ['Get your free quote', 'Tell us your dates, your group and where you are flying from.'],
                ['Review your itinerary', 'Receive a personalized itinerary and pricing.'],
                ['Travel with confidence', 'We take care of hotels, transport and experiences.'],
            ], ['title', 'text'])]),
            self::s('cta_banner', ['heading' => $c['cta_heading'], 'text' => $c['cta_text']]),
        ];
    }
}
