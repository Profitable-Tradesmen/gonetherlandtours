<?php

namespace Database\Seeders;

use App\Models\Destination;
use App\Models\Faq;
use App\Models\Package;
use App\Models\TourType;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

/**
 * Starter catalogue. EVERYTHING here is SAMPLE data: prices are blank on purpose
 * ([CONFIRM: price] shows on the site) and testimonials are marked placeholders.
 * See docs/reference/CONTENT_TO_CONFIRM.md.
 */
class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        if (TourType::count()) {
            return;
        }

        $types = [
            ['Self Drive', 'self-drive', 'car', 'Pick up your car and follow a carefully planned route at your own pace, with hotels booked for you.', 'images/tour-self-drive.webp', 'A scenic Dutch country road lined with trees'],
            ['Chauffeur Driven', 'chauffeur-driven', 'guide', 'Relax in comfort while a local driver-guide handles the roads and shares the stories behind each stop.', 'images/tour-chauffeur.webp', 'A canal-side street in a Dutch town'],
            ['Coach Tours', 'coach-tours', 'coach', 'Travel in a small group with a guide, with sightseeing, transport and accommodation taken care of.', 'images/tour-coach.webp', 'Colourful Dutch tulip fields in spring'],
        ];
        $typeIds = [];
        foreach ($types as $i => [$name, $slug, $icon, $summary, $image, $alt]) {
            $typeIds[$slug] = TourType::create(compact('name', 'slug', 'icon', 'summary', 'image') + ['image_alt' => $alt, 'sort_order' => $i + 1])->id;
        }

        $dests = [
            ['Amsterdam & the Cities', 'amsterdam-and-the-cities', 'Canals, world-class museums and historic Dutch cities such as Utrecht, Haarlem and Delft.', 'images/dest-amsterdam.webp', 'Amsterdam canal houses reflected in the water'],
            ['Tulips & Countryside', 'tulips-and-countryside', 'Flower fields, windmills and quiet villages in the Dutch countryside.', 'images/dest-tulips.webp', 'A windmill beside a Dutch canal'],
            ['Zeeland & the Delta', 'zeeland-and-the-delta', 'Coastline, sea defences and charming small towns in the south-west.', 'images/dest-zeeland.webp', 'Dutch coastline with dunes'],
        ];
        $destIds = [];
        foreach ($dests as $i => [$name, $slug, $summary, $image, $alt]) {
            $destIds[$slug] = Destination::create([
                'name' => $name, 'slug' => $slug, 'h1' => $name.' Tours', 'summary' => $summary,
                'body' => '<p>[CONFIRM: destination guide copy for '.e($name).'. Sample text only.]</p><p>'.e($summary).'</p>',
                'image' => $image, 'image_alt' => $alt, 'sort_order' => $i + 1,
                'seo_title' => $name.' Tours | '.'GoNetherlandTours',
                'meta_description' => 'Explore '.$name.' with GoNetherlandTours. '.$summary,
            ])->id;
        }

        $packages = [
            ['Sample: Tulip Season Self Drive', 'sample-tulip-season-self-drive', 'self-drive', 'tulips-and-countryside', 7, true],
            ['Sample: Amsterdam & Cities Self Drive', 'sample-amsterdam-cities-self-drive', 'self-drive', 'amsterdam-and-the-cities', 8, false],
            ['Sample: Dutch Highlights Chauffeur Tour', 'sample-dutch-highlights-chauffeur', 'chauffeur-driven', 'amsterdam-and-the-cities', 6, true],
            ['Sample: Zeeland Coast Chauffeur Tour', 'sample-zeeland-coast-chauffeur', 'chauffeur-driven', 'zeeland-and-the-delta', 7, false],
            ['Sample: Netherlands in Bloom Coach Tour', 'sample-netherlands-in-bloom-coach', 'coach-tours', 'tulips-and-countryside', 8, true],
            ['Sample: Cities & Canals Coach Tour', 'sample-cities-canals-coach', 'coach-tours', 'amsterdam-and-the-cities', 9, false],
        ];
        foreach ($packages as $i => [$title, $slug, $type, $dest, $nights, $featured]) {
            Package::create([
                'title' => $title, 'slug' => $slug, 'tour_type_id' => $typeIds[$type], 'destination_id' => $destIds[$dest],
                'nights' => $nights, 'price_from' => null, 'currency' => 'EUR',
                'summary' => 'SAMPLE PACKAGE. [CONFIRM: real summary]. A '.$nights.'-night '.strtolower(str_replace('-', ' ', $type)).' itinerary in the Netherlands.',
                'description' => '<p>[CONFIRM: full package description. This is sample data to show the layout.]</p>',
                'highlights' => ['[CONFIRM: highlight one]', '[CONFIRM: highlight two]', '[CONFIRM: highlight three]'],
                'itinerary' => [
                    ['title' => 'Day 1: Arrival in Amsterdam', 'body' => '[CONFIRM: day description]'],
                    ['title' => 'Day 2: [CONFIRM: day title]', 'body' => '[CONFIRM: day description]'],
                    ['title' => 'Day 3: [CONFIRM: day title]', 'body' => '[CONFIRM: day description]'],
                ],
                'inclusions' => ['[CONFIRM: inclusion]', '[CONFIRM: inclusion]'],
                'exclusions' => ['[CONFIRM: exclusion]', '[CONFIRM: exclusion]'],
                'faqs' => [['question' => '[CONFIRM: package question]', 'answer' => '[CONFIRM: answer]']],
                'is_published' => true, 'is_featured' => $featured, 'is_sample' => true, 'sort_order' => $i + 1,
                'seo_title' => $title, 'meta_description' => 'Sample package. Replace with real description.',
                'noindex' => true, // sample packages stay out of search until replaced
            ]);
        }

        $faqs = [
            ['When is the best time to visit the Netherlands?', 'Spring is popular for tulip season and summer for long, bright days. Autumn and winter are quieter. [CONFIRM: exact tulip season dates and advice]'],
            ['Do I need a visa to visit the Netherlands from the US?', 'Entry rules change. Please check the official Dutch government and US State Department websites before you travel. [CONFIRM: wording reviewed by the team]'],
            ['Can I drive in the Netherlands with a US licence?', 'Car hire rules vary by company. We will confirm licence requirements with your quote. [CONFIRM: licence and IDP requirements]'],
            ['What is included in a package?', 'Each package lists exactly what is included and excluded on its page. [CONFIRM: standard inclusions]'],
            ['How do I get a quote?', 'Answer a few quick questions or send an enquiry. A travel specialist will come back to you with a tailored quote.'],
            ['Can you tailor a trip for me?', 'Yes. Every itinerary can be adjusted to your dates, group size and interests.'],
        ];
        foreach ($faqs as $i => [$q, $a]) {
            Faq::create(['question' => $q, 'answer' => $a, 'sort_order' => $i + 1]);
        }

        foreach ([1, 2, 3] as $n) {
            Testimonial::create([
                'name' => '[PLACEHOLDER: traveller name]', 'location' => '[PLACEHOLDER: city, state]',
                'quote' => '[PLACEHOLDER REVIEW '.$n.': replace with a real, permitted customer review before launch.]',
                'is_placeholder' => true, 'sort_order' => $n,
            ]);
        }
    }
}
