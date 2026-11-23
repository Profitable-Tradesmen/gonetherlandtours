<?php

namespace Database\Seeders;

use App\Models\Destination;
use App\Models\Faq;
use App\Models\Package;
use App\Models\TourType;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

/**
 * Starter catalog. EVERYTHING here is SAMPLE data: prices are blank on purpose
 * (Price on request shows on the site) and testimonials are marked placeholders.
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
            ['Self Drive', 'self-drive', 'car', 'Pick up your car and follow a carefully planned route at your own pace, with hotels booked for you.', 'images/tour-self-drive.webp', 'A small car driving a quiet country road through Dutch polders and tulip fields'],
            ['Chauffeur Driven', 'chauffeur-driven', 'guide', 'Relax in comfort while a local driver-guide handles the roads and shares the stories behind each stop.', 'images/tour-chauffeur.webp', 'A chauffeur holding the door of a black van on a cobbled Dutch town square'],
            ['Coach Tours', 'coach-tours', 'coach', 'Travel in a small group with a guide, with sightseeing, transport and accommodation taken care of.', 'images/tour-coach.webp', 'A small tour minibus parked beside a canal in a charming Dutch village'],
        ];
        $typeIds = [];
        foreach ($types as $i => [$name, $slug, $icon, $summary, $image, $alt]) {
            $typeIds[$slug] = TourType::create(compact('name', 'slug', 'icon', 'summary', 'image') + ['h1' => $name.' Netherlands Tours', 'style_note' => ['self-drive' => 'Private: just you and your own party, at your own pace', 'chauffeur-driven' => 'Private: your own party with a local driver-guide', 'coach-tours' => 'Small group with a guide'][$slug] ?? null, 'body' => '<p>[CONFIRM: '.e($name).' page copy. Sample text only.]</p><p>'.e($summary).'</p>', 'image_alt' => $alt, 'sort_order' => $i + 1])->id;
        }

        $dests = [
            ['Amsterdam & the Cities', 'amsterdam-and-the-cities', 'Canals, world-class museums and historic Dutch cities such as Utrecht, Haarlem and Delft.', 'images/dest-amsterdam.webp', 'Amsterdam canal houses and a canal boat on a bright morning'],
            ['Tulips & Countryside', 'tulips-and-countryside', 'Flower fields, windmills and quiet villages in the Dutch countryside.', 'images/dest-tulips.webp', 'A traditional windmill beside rows of tulips at golden hour'],
            ['Zeeland & the Delta', 'zeeland-and-the-delta', 'Coastline, sea defenses and charming small towns in the south-west.', 'images/dest-zeeland.webp', 'A quiet Dutch harbor village with historic sailing boats in morning mist'],
        ];
        $destIds = [];
        foreach ($dests as $i => [$name, $slug, $summary, $image, $alt]) {
            $destIds[$slug] = Destination::create([
                'name' => $name, 'slug' => $slug, 'h1' => \App\Support\DestinationCopy::all()[$slug]['h1'] ?? $name.' Tours', 'summary' => $summary,
                'body' => \App\Support\DestinationCopy::all()[$slug]['body'] ?? '<p>[CONFIRM: destination guide copy for '.e($name).'. Sample text only.]</p><p>'.e($summary).'</p>',
                'image' => $image, 'image_alt' => $alt, 'sort_order' => $i + 1,
                'seo_title' => \App\Support\DestinationCopy::all()[$slug]['seo_title'] ?? $name.' Tours | GoNetherlandTours',
                'meta_description' => \App\Support\DestinationCopy::all()[$slug]['meta_description'] ?? 'Explore '.$name.' with GoNetherlandTours. '.$summary,
            ])->id;
        }

        \App\Support\SamplePackages::seed();   // three complete packages per tour type, with photos

        $faqs = array_map(fn ($f) => [$f[0], $f[1]], \App\Support\FaqCopy::all());
        foreach ($faqs as $i => [$q, $a]) {
            Faq::create(['question' => $q, 'answer' => $a, 'sort_order' => $i + 1]);
        }

        // SAMPLE reviews (is_placeholder = true): the home page labels them "Sample review". Replace with real, permitted reviews and untick the placeholder flag before launch.
        $reviews = [
            ['Sarah M.', 'Portland, Oregon', 'We wanted to see the real Netherlands, not just the postcard version. Our small group got to visit a cheese market, wander through quiet villages and ride bikes past the windmills. It felt personal from the first email to the last day.'],
            ['David & Karen L.', 'Charlotte, North Carolina', 'The itinerary was beautifully planned. Every stop made sense, nothing felt rushed, and we found little towns we would never have found on our own. We have already recommended it to our friends.'],
            ['Michelle T.', 'Austin, Texas', 'Traveling in a small group made all the difference. Our guide adjusted the plan to what we enjoyed most, and we came home with real friendships and a camera full of tulips. Planning the trip was easy too.'],
        ];
        foreach ($reviews as $i => [$name, $location, $quote]) {
            Testimonial::create(['name' => $name, 'location' => $location, 'quote' => $quote, 'is_placeholder' => true, 'sort_order' => $i + 1]);
        }
    }
}
