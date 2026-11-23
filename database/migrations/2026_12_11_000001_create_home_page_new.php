<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * "Home Page New" (/home-page-new): a second home page laid out like the reference site (goeurotours.com): centered caps hero with
 * destination chips, why-us split, featured tours, numbers band, destination cards, travel-your-way panels, photo collection,
 * reviews, a "travel with confidence" band and a closing call to action. Same palette and fonts as the current site.
 * It is published but set to noindex (it repeats the home page), so it can be reviewed by link without competing with the real home page.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('pages')->where('slug', 'home-page-new')->exists()) {
            return;
        }
        $s = fn (string $type, array $data) => ['id' => Str::lower(Str::random(8)), 'type' => $type, 'visible' => true, 'data' => $data];
        $dest = '/destinations/';

        $sections = [
            $s('hero_centered', [
                'image' => 'images/hero-home.webp', 'image_alt' => 'Traditional Dutch windmills beside green polders under a blue sky',
                'eyebrow' => 'Premier Netherlands Tours',
                'heading' => 'Unforgettable *Netherlands* Escapes',
                'text' => 'Expertly crafted itineraries. Iconic Dutch destinations. Seamless travel from the US and Canada.',
                'primary_label' => 'Get a free quote', 'secondary_label' => 'Explore the Netherlands', 'secondary_url' => '/destinations',
                'chips_label' => 'Where to?',
                'chips' => [
                    ['label' => 'Amsterdam', 'url' => $dest.'amsterdam-and-the-cities'], ['label' => 'Haarlem', 'url' => $dest.'amsterdam-and-the-cities'],
                    ['label' => 'Delft', 'url' => $dest.'amsterdam-and-the-cities'], ['label' => 'Utrecht', 'url' => $dest.'amsterdam-and-the-cities'],
                    ['label' => 'Tulip Fields', 'url' => $dest.'tulips-and-countryside'], ['label' => 'Kinderdijk', 'url' => $dest.'tulips-and-countryside'],
                    ['label' => 'Giethoorn', 'url' => $dest.'tulips-and-countryside'], ['label' => 'Zeeland', 'url' => $dest.'zeeland-and-the-delta'],
                ],
            ]),
            $s('split_list', [
                'image1' => 'images/Intro_image_split.webp', 'image1_alt' => 'A quiet Dutch canal town at dawn with gabled brick houses reflected in still water',
                'image2' => 'images/Intro_image_split1.webp', 'image2_alt' => 'A traditional windmill beside rows of tulips at golden hour',
                'badge' => 'Free, no-obligation quote',
                'eyebrow' => 'Why travel with GoNetherlandTours',
                'heading' => 'Your Netherlands vacation should be *truly unforgettable.*',
                'items' => [
                    ['title' => 'Custom Netherlands Itineraries', 'text' => 'Every route is built around your dates, pace and interests, with your flights from the US or Canada in mind.'],
                    ['title' => 'Quality Stays & Local Access', 'text' => 'Hotels chosen for their location, plus the local know-how to find quiet canal towns beyond the usual hotspots.'],
                    ['title' => 'Real Support', 'text' => 'A Netherlands travel specialist plans your trip and is there to help before you go and while you travel.'],
                ],
                'button_label' => 'Get a free quote',
            ]),
            $s('packages_tabs', [
                'eyebrow' => 'Featured Netherlands itineraries', 'heading' => 'Our *signature* Netherlands tours', 'layout' => 'tabs',
                'intro' => 'Self drive, chauffeur driven and small-group tours, all fully customizable to your dates and interests.',
                'all_label' => 'All Packages', 'button_label' => 'See all packages',
            ]),
            $s('stats_band', [
                'eyebrow' => 'The GoNetherlandTours difference', 'heading' => 'Why travel *with us?*',
                'text' => 'Local Netherlands specialists, clear quotes and a trip planned around you from the first inquiry to your flight home.',
                'items' => [
                    ['value' => '3', 'label' => 'Ways to travel'], ['value' => '3', 'label' => 'Regions to explore'],
                    ['value' => '1', 'label' => 'Specialist plans your trip'], ['value' => '100%', 'label' => 'Free, no-obligation quotes'],
                ],
                'button_label' => 'Get a free quote',
            ]),
            $s('destination_cards', [
                'eyebrow' => 'Destinations', 'heading' => 'Explore our top *Netherlands destinations*',
                'text' => 'From the canals of Amsterdam to the tulip fields and the quiet Zeeland coast.',
                'explore_label' => 'Explore', 'quote_label' => 'Get a quote', 'all_label' => 'View more destinations', 'all_url' => '/destinations',
            ]),
            $s('image_strip', [
                'eyebrow' => 'Travel your way', 'heading' => 'Your journey. *Your way.*', 'button_label' => 'Plan this trip',
                'items' => [
                    ['title' => 'First-time travel', 'text' => 'The essential Netherlands, paced so you see more and rush less.', 'image' => 'images/first_time_visitors.webp', 'image_alt' => 'Amsterdam canal houses and a canal boat on a bright morning'],
                    ['title' => 'Family escapes', 'text' => 'Windmills, cheese markets and hands-on traditions the whole group will remember.', 'image' => 'images/families.webp', 'image_alt' => 'A traditional windmill beside a quiet canal at Kinderdijk'],
                    ['title' => 'Couples & honeymoons', 'text' => 'Quiet canal towns, long dinners and time to simply enjoy each other.', 'image' => 'images/couples.webp', 'image_alt' => 'A Dutch canal lined with terraces and a boat gliding past historic houses'],
                ],
            ]),
            $s('gallery', [
                'eyebrow' => 'The Netherlands collection', 'heading' => 'Moments *worth traveling for*',
                'items' => [
                    ['caption' => 'Amsterdam canals', 'image' => 'images/first_time_visitors.webp', 'image_alt' => 'Amsterdam canal houses and a canal boat on a bright morning'],
                    ['caption' => 'Canal-side terraces', 'image' => 'images/couples.webp', 'image_alt' => 'A Dutch canal lined with terraces and a boat gliding past historic houses'],
                    ['caption' => 'Kinderdijk windmills', 'image' => 'images/families.webp', 'image_alt' => 'A traditional windmill beside a quiet canal at Kinderdijk'],
                    ['caption' => 'Cheese tasting', 'image' => 'images/culture_lovers.webp', 'image_alt' => 'A plate of Dutch cheese cubes for tasting'],
                    ['caption' => 'Polders and villages', 'image' => 'images/off_the_beaten_path.webp', 'image_alt' => 'An aerial view of Dutch polders, rivers and quiet villages'],
                    ['caption' => 'Special occasions', 'image' => 'images/special_occasions.webp', 'image_alt' => 'A restaurant table set with drinks in a warm, candle-lit room'],
                ],
                'closing_heading' => 'Seen enough to start planning?', 'button_label' => 'Get a free quote',
            ]),
            $s('testimonials', ['variant' => 'light', 'eyebrow' => 'Guest reviews', 'heading' => 'Traveler stories, *honestly shared.*']),
            $s('why_band', [
                'eyebrow' => 'Travel with confidence', 'heading' => 'Planning made *simple and secure*',
                'intro' => 'Know what you are paying for and when, before you commit.', 'button_label' => 'Get a free quote',
                'items' => [
                    ['icon' => 'money', 'title' => 'Clear quotes in euros', 'text' => 'Know what is included before you decide, with no hidden fees.'],
                    ['icon' => 'lock', 'title' => 'Simple payment schedule', 'text' => 'A deposit when you book and the balance 60 days before departure.'],
                    ['icon' => 'check', 'title' => 'Confirmed in writing', 'text' => 'Your itinerary and price are confirmed in writing before you travel.'],
                    ['icon' => 'phone', 'title' => 'Support along the way', 'text' => 'A real person to help before you leave and while you travel.'],
                ],
            ]),
            $s('cta_banner', [
                'eyebrow' => 'Discover the Netherlands', 'image' => 'images/fina_cta_bg.webp', 'image_alt' => 'Tulip fields and a distant windmill at sunset',
                'heading' => 'Stop dreaming. *Start packing.*',
                'text' => 'Spring is the busiest season for visitors from the US and Canada, so it pays to book early. Get a free, no-obligation quote today.',
                'button_label' => 'Get a free quote', 'phone_label' => 'Call',
            ]),
        ];

        DB::table('pages')->insert([
            'slug' => 'home-page-new', 'title' => 'Home Page New', 'h1' => 'Unforgettable Netherlands Escapes',
            'seo_title' => 'Netherlands Tours for US and Canadian Travelers | GoNetherlandTours',
            'meta_description' => 'Custom self drive, chauffeur driven and small-group Netherlands tours for US and Canadian travelers. Get a free quote.',
            'noindex' => true, 'is_published' => true, 'is_system' => false,
            'sections' => json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('pages')->where('slug', 'home-page-new')->delete();
    }
};
