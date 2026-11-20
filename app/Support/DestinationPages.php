<?php

namespace App\Support;

use App\Models\Destination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Sections of the destination pages (/destinations/<slug>). Like the tour type pages, each destination has a companion system page
 * (slug "dest-<destination slug>") whose sections are edited in Admin > Pages. The hero (heading, summary, photo) still comes from
 * Admin > Destinations. A destination without a companion page keeps the older layout (hero, guide text, packages).
 */
class DestinationPages
{
    public static function slugFor(Destination $d): string
    {
        return 'dest-'.$d->slug;
    }

    /** Creates the companion page of every destination that has none. Never touches an existing page. Returns how many were created. */
    public static function ensureAll(): int
    {
        $created = 0;
        foreach (Destination::orderBy('sort_order')->get() as $d) {
            if (DB::table('pages')->where('slug', self::slugFor($d))->exists()) {
                continue;
            }
            $now = now();
            DB::table('pages')->insert([
                'slug' => self::slugFor($d), 'title' => $d->name.' page', 'h1' => $d->name,
                'sections' => json_encode(self::sections($d), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'is_published' => true, 'is_system' => true, 'noindex' => true,   // the public page is /destinations/<slug>; this record only holds the sections
                'created_at' => $now, 'updated_at' => $now,
            ]);
            $created++;
        }

        return $created;
    }

    private static function s(string $type, array $data): array
    {
        return ['id' => Str::lower(Str::random(8)), 'type' => $type, 'visible' => true, 'data' => $data + Sections::defaults($type)];
    }

    private static function rows(array $rows, array $keys): array
    {
        return array_map(fn ($r) => array_combine($keys, array_pad(array_values($r), count($keys), '')), $rows);
    }

    /** US English copy per destination. Unknown destinations get a neutral version built from their name. */
    public static function copy(Destination $d): array
    {
        $name = $d->name;

        return match ($d->slug) {
            'amsterdam-and-the-cities' => [
                'strip' => [
                    ['city', 'Canals and culture', 'The canal ring, the Rijksmuseum and the Van Gogh Museum.'],
                    ['map', 'Old towns close together', 'Haarlem, Delft and Utrecht are short hops apart.'],
                    ['lock', 'Busy sights booked ahead', 'Timed entry reserved for you where it helps.'],
                ],
                'eyebrow' => 'Discover Amsterdam & the Cities', 'heading' => 'The Netherlands at its *most classic*',
                'points' => [
                    ['Amsterdam', 'The 17th-century canal ring, world-class museums and a canal cruise at golden hour.'],
                    ['Haarlem', 'A relaxed old town with a grand market square and almshouse courtyards.'],
                    ['Delft', 'Blue-and-white pottery, a medieval center and canals that look like a painting.'],
                    ['Utrecht', 'A city of two levels, with canal-side wharf cellars and a landmark cathedral tower.'],
                ],
                'button' => 'Start planning my city trip',
                'image2' => 'images/couples.webp', 'image2_alt' => 'A Dutch canal lined with terraces and a boat gliding past historic houses',
                'pk_eyebrow' => 'Tours that visit the cities', 'pk_heading' => 'Tours in '.$name,
                'when_image' => 'images/journal-2.webp', 'when_alt' => 'A quiet pier over calm water at dusk, with the Amsterdam skyline in the distance',
                'when_heading' => 'When to *go*',
                'when_text' => 'Spring brings tulips and long, bright days, summer is lively and warm, and autumn is quieter with golden light on the water. Popular sights such as the Anne Frank House and the major museums sell out, so we reserve timed entry for you well ahead.',
                'how_image' => 'images/first_time_visitors.webp', 'how_alt' => 'Amsterdam canal houses and a canal boat on a bright morning',
                'how_text' => 'Cities are easy to explore on foot, so parking and driving distances are planned with care. Typical stops include Amsterdam, Haarlem, Utrecht, Delft and Rotterdam.',
                'cta_heading' => 'Ready to explore Amsterdam & the Cities?',
            ],
            'tulips-and-countryside' => [
                'strip' => [
                    ['tulip', 'Tulip season, timed well', 'We plan around the typical April bloom and adjust as it unfolds.'],
                    ['car', 'Windmills and markets', 'Kinderdijk, the Zaanse Schans and weekly cheese markets.'],
                    ['heart', 'Quiet villages', 'Early-morning lanes and harbors before the day trippers.'],
                ],
                'eyebrow' => 'Discover the Dutch countryside', 'heading' => 'Windmills, flower fields and *slow villages*',
                'points' => [
                    ['Tulip season', 'The Bollenstreek fields bloom in bands of color, usually from early April to early May.'],
                    ['Windmills', 'From the 19 windmills of Kinderdijk to the working mills of the Zaanse Schans.'],
                    ['Cheese markets', 'Alkmaar on Friday mornings (April to September) and Gouda on Thursday mornings (April to August).'],
                    ['Quiet villages', 'Early-morning canal lanes and village harbors, such as Giethoorn.'],
                ],
                'button' => 'Start planning my countryside trip',
                'image2' => 'images/culture_lovers.webp', 'image2_alt' => 'A plate of Dutch cheese cubes for tasting',
                'pk_eyebrow' => 'Tours that visit the countryside', 'pk_heading' => 'Tours in '.$name,
                'when_image' => 'images/gallery-2.webp', 'when_alt' => 'Bunches of colorful tulips ready to be sold at a flower stall',
                'when_heading' => 'When to *go*',
                'when_text' => 'Tulip timing changes with the weather each year, so we plan around the typical bloom. Outside tulip season, summer and early autumn bring green fields, open-air markets and long evenings.',
                'how_image' => 'images/feature_band.webp', 'how_alt' => 'A green wooden house and bridge reflected in a still canal at dusk',
                'how_text' => 'The countryside rewards a slower pace, with plenty of places to stop. Typical stops include the tulip fields, the windmills of Kinderdijk and the canals of Giethoorn.',
                'cta_heading' => 'Ready to see the Dutch countryside?',
            ],
            'zeeland-and-the-delta' => [
                'strip' => [
                    ['summer', 'Coast and beaches', 'Wide North Sea beaches and paths through the dunes.'],
                    ['shield', 'The Delta Works', 'Cross the Oosterschelde storm surge barrier.'],
                    ['city', 'Historic harbor towns', 'Middelburg, Veere and Zierikzee at an easy pace.'],
                ],
                'eyebrow' => 'Discover Zeeland and the Delta', 'heading' => 'Where the land *meets the sea*',
                'points' => [
                    ['The Delta Works', 'See the vast storm surge barriers that protect the country from the North Sea.'],
                    ['Historic towns', 'Middelburg, Veere and Zierikzee, with old town halls, harbors and quiet cobbled streets.'],
                    ['Beaches and dunes', 'Wide beaches and walking paths through the dunes.'],
                    ['Local flavors', 'Zeeland mussels and oysters in season, and coastal cafes with a view.'],
                ],
                'button' => 'Start planning my Zeeland trip',
                'image2' => 'images/feature_band.webp', 'image2_alt' => 'A green wooden house and bridge reflected in a still canal at dusk',
                'pk_eyebrow' => 'Tours that visit Zeeland', 'pk_heading' => 'Tours in '.$name,
                'when_image' => 'images/off_the_beaten_path.webp', 'when_alt' => 'An aerial view of Dutch polders, rivers and quiet villages',
                'when_heading' => 'When to *go*',
                'when_text' => 'Late spring to early autumn is the most comfortable time for the coast, with long days for exploring. The shoulder months are quieter and the light over the water can be beautiful.',
                'how_image' => 'images/first_time_visitors.webp', 'how_alt' => 'Amsterdam canal houses and a canal boat on a bright morning',
                'how_text' => 'Distances are short, so you can see a lot without rushing. Typical stops include Middelburg, Veere and Zierikzee.',
                'cta_heading' => 'Ready to explore Zeeland?',
            ],
            default => [
                'strip' => [
                    ['map', 'Planned with care', 'Routes and hotels worked out for you before you go.'],
                    ['phone', 'Real support', 'A real person to help before and during your trip.'],
                    ['money', 'Clear, upfront quotes', 'Know what is included before you decide.'],
                ],
                'eyebrow' => 'Discover '.$name, 'heading' => $name.' with *real support*',
                'points' => [['Planned around you', 'Itineraries shaped by your dates, pace and interests.'], ['Hotels arranged', 'Accommodation is booked in advance.'], ['Clear plans', 'Day by day itineraries so you always know what comes next.']],
                'button' => 'Start planning my trip',
                'image2' => 'images/Intro_image_split1.webp', 'image2_alt' => 'A traditional windmill beside rows of tulips at golden hour',
                'pk_eyebrow' => 'Signature journeys', 'pk_heading' => 'Tours in '.$name,
                'when_image' => 'images/Intro_image_split.webp', 'when_alt' => 'Bicycles parked beside a canal in a Dutch city',
                'when_heading' => 'When to *go*', 'when_text' => 'Tell us when you would like to travel and we will suggest the best timing.',
                'how_image' => 'images/feature_band.webp', 'how_alt' => 'A green wooden house and bridge reflected in a still canal at dusk',
                'how_text' => 'We plan the route around your dates, pace and interests.',
                'cta_heading' => 'Ready to explore '.$name.'?',
            ],
        };
    }

    public static function sections(Destination $d): array
    {
        $c = self::copy($d);
        $name = $d->name;

        return [
            self::s('value_strip', ['items' => self::rows($c['strip'], ['icon', 'title', 'text'])]),
            self::s('split_list', [
                'image1' => $d->image ?: 'images/Intro_image_split.webp', 'image1_alt' => $d->image_alt ?: '',
                'image2' => $c['image2'], 'image2_alt' => $c['image2_alt'],
                'badge' => 'Free, no-obligation quote', 'eyebrow' => $c['eyebrow'], 'heading' => $c['heading'],
                'items' => self::rows($c['points'], ['title', 'text']), 'button_label' => $c['button'],
            ]),
            self::s('tour_packages', ['eyebrow' => $c['pk_eyebrow'], 'heading' => $c['pk_heading'], 'intro' => 'Handcrafted itineraries you can adjust to your dates and interests.', 'empty_text' => 'No tours in '.$name.' are published yet. Ask us for a tailored quote.']),
            self::s('why_us', []),
            self::s('split_columns', [
                'left_image' => $c['when_image'], 'left_image_alt' => $c['when_alt'], 'left_heading' => $c['when_heading'], 'left_text' => $c['when_text'],
                'right_image' => $c['how_image'], 'right_image_alt' => $c['how_alt'], 'right_heading' => 'How you can *travel*', 'right_text' => $c['how_text'],
                'right_bullets' => array_map(fn ($t) => ['text' => $t], ['Self drive: hotels booked for you, your own pace', 'Chauffeur driven: a private local driver-guide', 'Coach tour: a small group with a guide']),
            ]),
            self::s('promo_band', ['eyebrow' => 'Crafted for you', 'heading' => 'Ready to plan your *'.$name.' trip?*', 'text' => 'Every great Netherlands trip starts with a conversation. Tell us what you have in mind and we will build the route around you.', 'points' => array_map(fn ($t) => ['text' => $t], ['Netherlands travel specialists', 'Fully customizable itineraries', 'Support before and during your trip']), 'button_label' => 'Request a custom tour']),
            self::s('testimonials', ['variant' => 'dark', 'eyebrow' => 'Reviews', 'heading' => 'What our guests say', 'limit' => 3]),
            self::s('steps', ['style' => 'timeline', 'eyebrow' => 'Simple', 'heading' => '3 easy steps to book your trip', 'items' => self::rows([
                ['Get your free quote', 'Tell us your travel plans and preferences.'],
                ['Review your itinerary', 'Receive a personalized itinerary and pricing.'],
                ['Travel with confidence', 'We take care of hotels, transport and experiences.'],
            ], ['title', 'text'])]),
            self::s('cta_banner', ['heading' => $c['cta_heading'], 'text' => 'Get in touch for a trip designed around you, with a clear, no-obligation quote.']),
        ];
    }
}
