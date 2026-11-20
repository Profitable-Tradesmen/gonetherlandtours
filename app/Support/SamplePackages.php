<?php

namespace App\Support;

use App\Models\Destination;
use App\Models\Media;
use App\Models\Package;
use App\Models\TourType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Starter packages: three per tour type, with complete (but illustrative) itineraries, inclusions, FAQs and photos.
 * Modeled on typical Netherlands tour programs. Replace prices, routes and wording in Admin > Packages before launch.
 * Photos are public-domain (CC0) images stored as media/2026/pkg-{slug}-{n}-{480|960|1600}.webp.
 */
class SamplePackages
{
    /** Replace the old sample packages with the nine below. Safe to run more than once. */
    public static function seed(): void
    {
        $types = TourType::pluck('id', 'slug');
        $dests = Destination::pluck('id', 'slug');
        if ($types->isEmpty() || $dests->isEmpty()) {
            return;
        }

        $old = Package::where('is_sample', true)->orWhere('title', 'like', 'Sample:%')->pluck('id');
        if ($old->isNotEmpty()) {
            $mediaIds = DB::table('package_images')->whereIn('package_id', $old)->pluck('media_id');
            DB::table('package_images')->whereIn('package_id', $old)->delete();
            foreach (Media::whereIn('id', $mediaIds)->get() as $m) {
                foreach ((array) $m->widths as $w) {
                    Storage::disk('public')->delete($m->path.'-'.$w.'.webp');
                }
                $m->delete();
            }
            Package::whereIn('id', $old)->delete();
        }

        foreach (self::all() as $i => $p) {
            $package = Package::updateOrCreate(['slug' => $p['slug']], [
                'title' => $p['title'], 'tour_type_id' => $types[$p['type']], 'destination_id' => $dests[$p['dest']],
                'nights' => $p['nights'], 'price_from' => $p['price'], 'currency' => 'EUR', 'summary' => $p['summary'],
                'description' => $p['description'], 'highlights' => $p['highlights'], 'itinerary' => $p['itinerary'],
                'inclusions' => self::inclusions($p['type']), 'exclusions' => self::exclusions($p['type']), 'faqs' => self::faqs($p['type']),
                'seo_title' => $p['title'].' | GoNetherlandTours', 'meta_description' => $p['summary'],
                'is_published' => true, 'is_featured' => true, 'is_sample' => false, 'noindex' => false, 'sort_order' => $i + 1,
            ]);

            if ($package->images()->exists()) {
                continue;
            }
            foreach ([1, 2, 3] as $n) {
                $path = 'media/2026/pkg-'.$p['slug'].'-'.$n;
                if (! Storage::disk('public')->exists($path.'-960.webp')) {
                    continue;
                }
                $media = Media::create(['path' => $path, 'widths' => [480, 960, 1600], 'width' => 1600, 'height' => 1000, 'alt' => $p['photo_alts'][$n - 1] ?? $p['title'], 'original_name' => 'pkg-'.$p['slug'].'-'.$n.'.webp']);
                $package->images()->attach($media->id, ['sort_order' => $n]);
            }
        }
    }

    public static function inclusions(string $type): array
    {
        return [
            'self-drive' => [
                'Hotel accommodation with daily breakfast, chosen to match your route',
                'Rental car with unlimited mileage and standard insurance',
                'Printed route book with daily driving directions and parking tips',
                'Travel app with offline maps and suggested stops',
                'Pre-booked tickets for the attractions marked in the itinerary',
                '24/7 phone support while you are on the road',
            ],
            'chauffeur-driven' => [
                'Private vehicle with an experienced English-speaking driver-guide',
                'Hotel accommodation with daily breakfast',
                'Airport meet-and-greet and arrival transfer',
                'Entrance tickets and any boat trips listed in the itinerary',
                'Bottled water and city maps in the vehicle each day',
                '24/7 phone support from our team',
            ],
            'coach-tours' => [
                'Small-group touring (up to 16 travelers) by modern coach',
                'Local English-speaking tour guide throughout',
                'Hotel accommodation with daily breakfast',
                'Welcome and farewell dinners with local specialties',
                'Entrance fees and boat trips listed in the itinerary',
                'Luggage handling at every hotel',
            ],
        ][$type];
    }

    public static function exclusions(string $type): array
    {
        $common = ['International flights', 'Travel insurance', 'Meals not listed in the itinerary', 'Personal expenses and optional activities'];

        return $type === 'self-drive'
            ? array_merge(['Fuel, parking and road tolls'], $common)
            : array_merge(['Tips for your guide and driver (optional)'], $common);
    }

    public static function faqs(string $type): array
    {
        return [
            'self-drive' => [
                ['question' => 'Do I need an International Driving Permit?', 'answer' => 'Most visitors can drive with a valid license from their home country. Some countries require an International Driving Permit, and we confirm the rules for your license with your quote.'],
                ['question' => 'Is driving in the Netherlands difficult?', 'answer' => 'Roads are well signposted and in excellent condition. Cities have low speed limits and many cyclists, so we plan parking at the edge of busy centers and give clear daily directions.'],
                ['question' => 'Can the route be changed?', 'answer' => 'Yes. Every self drive can be shortened, extended or adjusted to your dates, pace and interests. Tell us what you would like and we will build it into your quote.'],
            ],
            'chauffeur-driven' => [
                ['question' => 'Is the tour fully private?', 'answer' => 'Yes. The vehicle, driver-guide and itinerary are for your party only, so you can set the pace and linger wherever you like.'],
                ['question' => 'Does the driver speak English?', 'answer' => 'All of our driver-guides speak fluent English and know the regions well. Other languages can be arranged on request.'],
                ['question' => 'How many people can travel?', 'answer' => 'Our standard vehicles seat up to 7 travelers. Larger groups can be accommodated with a second vehicle or a minibus.'],
            ],
            'coach-tours' => [
                ['question' => 'How big are the groups?', 'answer' => 'Groups are limited to 16 travelers, so everyone gets a window seat, plenty of time with the guide and easy access to small towns that large coaches cannot reach.'],
                ['question' => 'Is there free time on the tour?', 'answer' => 'Yes. Most afternoons and evenings are free, and your guide is always happy to suggest restaurants, shops and walks.'],
                ['question' => 'Can I join as a solo traveler?', 'answer' => 'Absolutely. Solo travelers are welcome, and single rooms are available for a supplement.'],
            ],
        ][$type];
    }

    public static function all(): array
    {
        return [
            // ---------------------------------------------------------------- SELF DRIVE
            [
                'slug' => 'tulip-route-self-drive', 'title' => 'Tulip Route Self Drive', 'type' => 'self-drive', 'dest' => 'tulips-and-countryside', 'nights' => 7, 'price' => 1490,
                'summary' => 'Seven nights through the bulb region, Haarlem, Zaanse Schans and Kinderdijk, timed for the tulip season.',
                'photo_alts' => ['Yellow and red tulips in bloom at Keukenhof', 'Buckets of fresh tulips and spring flowers at a flower market', 'A traditional windmill beside a quiet canal'],
                'description' => '<p>Drive at your own pace through the heart of the Dutch bulb region, where fields of tulips, hyacinths and daffodils paint the countryside in stripes of color. Your route links charming small towns, family-run hotels and the best viewpoints, with every booking and parking detail taken care of.</p><p>Between the flowers you will visit working windmills, taste farmhouse cheese and stroll historic old towns, with plenty of time to stop whenever a view catches your eye.</p>',
                'highlights' => ['A full day at the Keukenhof gardens', 'Cycling through the bulb fields around Lisse', 'Windmills and wooden houses at Zaanse Schans', 'Kinderdijk UNESCO windmills', 'Farmhouse cheese tasting near Gouda'],
                'itinerary' => [
                    ['title' => 'Day 1: Arrival in Amsterdam', 'body' => 'Collect your rental car at Schiphol Airport and drive to your hotel in Haarlem. Settle in and stroll the Grote Markt for dinner.'],
                    ['title' => 'Day 2: The bulb region and Keukenhof', 'body' => 'Spend the day at the Keukenhof gardens, then follow the scenic flower route through Lisse and Hillegom to see the open fields.'],
                    ['title' => 'Day 3: Zaanse Schans and Edam', 'body' => 'Visit the windmills and wooden houses of Zaanse Schans, then drive to Edam for cheese, canals and a waterside lunch.'],
                    ['title' => 'Day 4: Amsterdam at leisure', 'body' => 'Park at the edge of the city and take the tram into Amsterdam for a canal cruise and time at your own pace.'],
                    ['title' => 'Day 5: Gouda and the Green Heart', 'body' => 'Drive south through the Green Heart to Gouda for the town hall, the cheese market and a tasting at a local farm.'],
                    ['title' => 'Day 6: Kinderdijk windmills', 'body' => 'Walk or cycle among 19 historic windmills at Kinderdijk, then continue to Delft for its canals and blue pottery.'],
                    ['title' => 'Day 7: Leiden and the coast', 'body' => 'Explore the canals of Leiden and finish the day on the dunes at Noordwijk or Katwijk.'],
                    ['title' => 'Day 8: Departure', 'body' => 'Drive to Schiphol Airport, return your car and fly home with a camera full of flowers.'],
                ],
            ],
            [
                'slug' => 'amsterdam-historic-cities-self-drive', 'title' => 'Amsterdam & Historic Cities Self Drive', 'type' => 'self-drive', 'dest' => 'amsterdam-and-the-cities', 'nights' => 8, 'price' => 1690,
                'summary' => 'Eight nights linking Amsterdam, Haarlem, Utrecht, Delft and Leiden, with parking and city access handled for you.',
                'photo_alts' => ['Tall gabled canal houses reflected in an Amsterdam canal', 'A historic canal with terraces and old houses in Utrecht', 'The ornate historic town hall in Delft'],
                'description' => '<p>Discover the Netherlands through its historic cities, each with its own canals, museums and character. You will drive short, easy distances, so there is time to wander, shop and linger over coffee.</p><p>Every hotel is chosen for its location and parking, so you can leave the car behind and explore on foot.</p>',
                'highlights' => ['Canal cruise through Amsterdam', 'Rijksmuseum or Van Gogh Museum visit', 'Haarlem\'s Grote Markt and Frans Hals Museum', 'Utrecht\'s Oudegracht and its wharf cellars', 'Delft\'s blue pottery and market square'],
                'itinerary' => [
                    ['title' => 'Day 1: Arrival in Amsterdam', 'body' => 'Arrive at Schiphol and settle into your central hotel. Take an evening stroll along the nearest canal.'],
                    ['title' => 'Day 2: Amsterdam highlights', 'body' => 'Visit the Rijksmuseum or Van Gogh Museum, then take a late-afternoon canal cruise.'],
                    ['title' => 'Day 3: Amsterdam at leisure', 'body' => 'Explore the Jordaan, the flower market and the nine streets at your own pace.'],
                    ['title' => 'Day 4: Haarlem', 'body' => 'Collect your rental car and drive to Haarlem for the Grote Markt, the Frans Hals Museum and the hidden courtyards (hofjes).'],
                    ['title' => 'Day 5: Leiden', 'body' => 'Wander the canals of Leiden, visit the windmill museum and enjoy the student-town atmosphere.'],
                    ['title' => 'Day 6: Delft and The Hague', 'body' => 'Browse the market square of Delft and its pottery workshops, then continue to The Hague for the Mauritshuis and the seaside at Scheveningen.'],
                    ['title' => 'Day 7: Rotterdam', 'body' => 'See the cube houses, the Markthal and the harbor, and finish with a boat tour of the port.'],
                    ['title' => 'Day 8: Utrecht', 'body' => 'Climb the Dom Tower and walk the Oudegracht before dinner on a canal-side terrace.'],
                    ['title' => 'Day 9: Departure', 'body' => 'Return your car at Schiphol and fly home.'],
                ],
            ],
            [
                'slug' => 'zeeland-coast-self-drive', 'title' => 'Zeeland Coast & Delta Self Drive', 'type' => 'self-drive', 'dest' => 'zeeland-and-the-delta', 'nights' => 6, 'price' => 1290,
                'summary' => 'Six nights along the Zeeland coast, from Middelburg and Veere to the storm surge barrier and quiet beaches.',
                'photo_alts' => ['A historic stepped-gable building in the town of Veere', 'Sand dunes and the North Sea coast in the Netherlands', 'Aerial view of Dutch waterways and polder fields'],
                'description' => '<p>Zeeland is the quiet south-west of the Netherlands, where the land meets the sea. This relaxed drive follows dikes, causeways and tiny harbor towns, with short distances and plenty of beaches, seafood and sunsets.</p><p>You will also see the Delta Works, one of the greatest feats of water engineering in the world.</p>',
                'highlights' => ['Historic Middelburg and its abbey', 'The harbor town of Veere', 'Delta Works storm surge barrier', 'Beach walks on the Zeeland coast', 'Fresh oysters and mussels'],
                'itinerary' => [
                    ['title' => 'Day 1: Arrival and drive to Zeeland', 'body' => 'Collect your car at Schiphol and drive south, crossing the Zeeland bridge to your hotel in Middelburg.'],
                    ['title' => 'Day 2: Middelburg', 'body' => 'Walk through the old town, the abbey and the Monday market (in season) before an afternoon at the beach in Domburg.'],
                    ['title' => 'Day 3: Veere and Walcheren', 'body' => 'Visit the stepped gables of Veere, the Scottish houses and the lakeside, then enjoy a seafood lunch.'],
                    ['title' => 'Day 4: Delta Works', 'body' => 'Drive across the storm surge barrier and visit the Delta Expo to see how the Dutch hold back the sea.'],
                    ['title' => 'Day 5: Zierikzee and Schouwen-Duiveland', 'body' => 'Explore the harbor and old gates of Zierikzee, then cycle along the dunes and beaches.'],
                    ['title' => 'Day 6: Dordrecht', 'body' => 'Continue north to Dordrecht for its harbor, historic churches and canal-side cafés.'],
                    ['title' => 'Day 7: Departure', 'body' => 'Drive to Schiphol Airport, return the car and head home.'],
                ],
            ],

            // ---------------------------------------------------------------- CHAUFFEUR DRIVEN
            [
                'slug' => 'dutch-highlights-chauffeur-tour', 'title' => 'Dutch Highlights Private Chauffeur Tour', 'type' => 'chauffeur-driven', 'dest' => 'amsterdam-and-the-cities', 'nights' => 6, 'price' => 2990,
                'summary' => 'Six nights of the classic Netherlands, with a private driver-guide and no parking, maps or stress.',
                'photo_alts' => ['Gabled houses and boats along an Amsterdam canal', 'A historic windmill on the river in Haarlem', 'A canal with terraces and a sightseeing boat'],
                'description' => '<p>See the very best of the Netherlands in comfort. Your private driver-guide meets you at the airport and stays with you throughout, sharing stories about each town, introducing you to local favorites and handling every parking, ticket and route detail.</p><p>You set the pace, so there is always time for another photo, a longer lunch or a detour to a village you spotted on the way.</p>',
                'highlights' => ['Private driver-guide for the whole trip', 'Amsterdam canal cruise and museum visit', 'Zaanse Schans windmills and cheese tasting', 'Haarlem, Delft and The Hague', 'Kinderdijk windmills by boat'],
                'itinerary' => [
                    ['title' => 'Day 1: Arrival in Amsterdam', 'body' => 'Your driver-guide meets you at Schiphol and takes you to your hotel. The evening is free, with restaurant recommendations ready.'],
                    ['title' => 'Day 2: Amsterdam', 'body' => 'A morning city tour introduces the canal ring, followed by a museum visit and an afternoon canal cruise.'],
                    ['title' => 'Day 3: Zaanse Schans and Edam', 'body' => 'Visit working windmills, a clog workshop and a cheese farm, then enjoy lunch by the canal in Edam.'],
                    ['title' => 'Day 4: Haarlem and the coast', 'body' => 'Tour Haarlem\'s old center and hofjes, then relax on the beach at Zandvoort.'],
                    ['title' => 'Day 5: Delft and The Hague', 'body' => 'Browse the blue pottery of Delft and see the Mauritshuis and Binnenhof in The Hague.'],
                    ['title' => 'Day 6: Kinderdijk and Rotterdam', 'body' => 'Take a boat among the historic windmills of Kinderdijk before an afternoon in modern Rotterdam.'],
                    ['title' => 'Day 7: Departure', 'body' => 'A private transfer takes you to Schiphol Airport for your flight home.'],
                ],
            ],
            [
                'slug' => 'windmills-tulips-cheese-chauffeur-tour', 'title' => 'Windmills, Tulips & Cheese Private Tour', 'type' => 'chauffeur-driven', 'dest' => 'tulips-and-countryside', 'nights' => 5, 'price' => 2490,
                'summary' => 'Five nights of windmills, flower fields and cheese markets with your own driver-guide.',
                'photo_alts' => ['Traditional windmills and green houses at Zaanse Schans', 'A plate of Dutch cheese cubes for tasting', 'Close-up of pink tulips in bloom'],
                'description' => '<p>A short, delicious tour built around the Netherlands\' best-loved images. Your driver-guide knows which windmills are open, where the tulips are blooming and which farm serves the best cheese.</p><p>The itinerary is gentle, with short drives and long stops, so it suits couples, families and multi-generational groups alike.</p>',
                'highlights' => ['Keukenhof gardens with a private driver', 'Cheese tasting at a family farm in Gouda', 'Windmills at Zaanse Schans and Kinderdijk', 'Fresh flowers at the Aalsmeer auction area', 'Free time in Amsterdam'],
                'itinerary' => [
                    ['title' => 'Day 1: Arrival in Amsterdam', 'body' => 'Meet your driver-guide at Schiphol and transfer to your hotel. Dinner is on your own.'],
                    ['title' => 'Day 2: Keukenhof and the bulb fields', 'body' => 'Spend the morning at Keukenhof and the afternoon driving the quiet roads between the colorful bulb fields.'],
                    ['title' => 'Day 3: Zaanse Schans and cheese', 'body' => 'See the windmills at work, then visit a cheese farm and taste several aged cheeses.'],
                    ['title' => 'Day 4: Gouda and Kinderdijk', 'body' => 'Tour the historic center of Gouda, then travel on to Kinderdijk to walk among the windmills.'],
                    ['title' => 'Day 5: Amsterdam at leisure', 'body' => 'A free day to cruise the canals, shop or visit a museum, with your driver on call.'],
                    ['title' => 'Day 6: Departure', 'body' => 'A private transfer to Schiphol Airport for your flight home.'],
                ],
            ],
            [
                'slug' => 'zeeland-delta-chauffeur-tour', 'title' => 'Zeeland & the Delta Private Chauffeur Tour', 'type' => 'chauffeur-driven', 'dest' => 'zeeland-and-the-delta', 'nights' => 7, 'price' => 3490,
                'summary' => 'Seven nights exploring Zeeland\'s coast and quiet towns with a private driver-guide.',
                'photo_alts' => ['A quiet canal and green wooden houses at dusk', 'Colorful historic houses along a Dutch street', 'Windmills and green fields under a blue sky'],
                'description' => '<p>Escape the crowds on a relaxed journey through the Netherlands\' quietest corner. Your driver-guide leads you along sea dikes and country lanes to historic harbors, fishing villages and the Delta Works.</p><p>The pace is unhurried, with leisurely lunches of fresh seafood and plenty of time on the beach.</p>',
                'highlights' => ['Private driver-guide through Zeeland', 'Delta Works and storm surge barrier', 'Middelburg, Veere and Zierikzee', 'Oyster tasting on the Oosterschelde', 'Kinderdijk windmills on the way back'],
                'itinerary' => [
                    ['title' => 'Day 1: Arrival and drive to Zeeland', 'body' => 'Your driver-guide meets you at Schiphol and drives you south to your hotel in Middelburg.'],
                    ['title' => 'Day 2: Middelburg', 'body' => 'Explore the abbey, the town hall and the market square with a local guide, followed by free time.'],
                    ['title' => 'Day 3: Veere and the coast', 'body' => 'Visit Veere\'s historic harbor and the beaches of Domburg, with lunch at a seaside café.'],
                    ['title' => 'Day 4: Delta Works', 'body' => 'Cross the storm surge barrier and tour the Delta Expo, then enjoy an oyster tasting on the Oosterschelde.'],
                    ['title' => 'Day 5: Zierikzee and Schouwen-Duiveland', 'body' => 'Walk the old harbor and gates of Zierikzee, then visit a local farm shop.'],
                    ['title' => 'Day 6: Free day', 'body' => 'Relax on the beach, rent bikes or spend a morning in the spa, with your driver available for an optional excursion.'],
                    ['title' => 'Day 7: Dordrecht and Kinderdijk', 'body' => 'Drive north to the historic harbor of Dordrecht and the windmills of Kinderdijk.'],
                    ['title' => 'Day 8: Departure', 'body' => 'Private transfer to Schiphol Airport for your flight home.'],
                ],
            ],

            // ---------------------------------------------------------------- COACH TOURS
            [
                'slug' => 'netherlands-in-bloom-small-group-tour', 'title' => 'Netherlands in Bloom Small-Group Tour', 'type' => 'coach-tours', 'dest' => 'tulips-and-countryside', 'nights' => 8, 'price' => 1890,
                'summary' => 'Eight nights through the tulip season with a local guide and a friendly group of up to 16.',
                'photo_alts' => ['Close-up of pink tulips in bloom', 'Yellow and red tulips in bloom at Keukenhof', 'Bunches of tulips at a flower market'],
                'description' => '<p>Join a small group of fellow travelers for the Netherlands\' most colorful season. A local guide leads you through the bulb fields, gardens and historic towns, with all transport and hotels arranged.</p><p>With a maximum of 16 travelers, the group moves easily and reaches places that big coaches cannot.</p>',
                'highlights' => ['Keukenhof gardens', 'Bulb fields and flower auction area', 'Zaanse Schans windmills', 'Canal cruise in Amsterdam', 'Welcome and farewell dinners'],
                'itinerary' => [
                    ['title' => 'Day 1: Arrival in Amsterdam', 'body' => 'Meet your guide and group at the hotel and enjoy a welcome dinner.'],
                    ['title' => 'Day 2: Amsterdam', 'body' => 'A guided walk through the canal ring, a canal cruise and an afternoon free for museums.'],
                    ['title' => 'Day 3: Zaanse Schans and Edam', 'body' => 'Visit the windmills, taste cheese and stroll the canals of Edam.'],
                    ['title' => 'Day 4: Keukenhof', 'body' => 'A full day at Keukenhof, with time for photos, lunch and the flower pavilions.'],
                    ['title' => 'Day 5: Bulb fields and Haarlem', 'body' => 'Drive through the open flower fields before exploring Haarlem\'s Grote Markt.'],
                    ['title' => 'Day 6: Gouda and Kinderdijk', 'body' => 'See the town hall and cheese market in Gouda, then walk among the windmills of Kinderdijk.'],
                    ['title' => 'Day 7: Delft and The Hague', 'body' => 'Browse the blue pottery of Delft and the historic center of The Hague.'],
                    ['title' => 'Day 8: Free day in Amsterdam', 'body' => 'Spend the day as you like, with your guide\'s suggestions for museums, shops and cafés.'],
                    ['title' => 'Day 9: Farewell and departure', 'body' => 'Breakfast and farewells, then transfer to Schiphol Airport.'],
                ],
            ],
            [
                'slug' => 'cities-canals-small-group-tour', 'title' => 'Cities & Canals Small-Group Tour', 'type' => 'coach-tours', 'dest' => 'amsterdam-and-the-cities', 'nights' => 9, 'price' => 2190,
                'summary' => 'Nine nights visiting the great Dutch cities by coach and canal boat with a local guide.',
                'photo_alts' => ['A historic canal with terraces and a sightseeing boat', 'Houseboats on an old Amsterdam canal', 'A busy Amsterdam street at dusk'],
                'description' => '<p>A comfortable group tour that covers the best of the Dutch cities, each with its own canals, architecture and food. You travel in a modern coach, with a guide who brings every stop to life.</p><p>Free afternoons leave room for museums, markets and long dinners.</p>',
                'highlights' => ['Amsterdam, Utrecht, Haarlem, Delft and Rotterdam', 'Three canal cruises', 'Museum entry in Amsterdam', 'Cheese tasting in Gouda', 'Group dinners with local specialties'],
                'itinerary' => [
                    ['title' => 'Day 1: Arrival in Amsterdam', 'body' => 'Meet your guide and group, then enjoy a welcome dinner.'],
                    ['title' => 'Day 2: Amsterdam', 'body' => 'A guided tour of the canal ring and the Jordaan, then an afternoon canal cruise.'],
                    ['title' => 'Day 3: Amsterdam museums', 'body' => 'Visit the Rijksmuseum, with the rest of the day free.'],
                    ['title' => 'Day 4: Haarlem and Zaanse Schans', 'body' => 'Tour Haarlem\'s old center and see the windmills of Zaanse Schans.'],
                    ['title' => 'Day 5: Utrecht', 'body' => 'Walk the Oudegracht, climb the Dom Tower and enjoy free time in the old town.'],
                    ['title' => 'Day 6: Gouda and Delft', 'body' => 'Taste cheese in Gouda and visit the pottery workshops of Delft.'],
                    ['title' => 'Day 7: The Hague', 'body' => 'See the Binnenhof, the Peace Palace and the Mauritshuis, then walk the beach at Scheveningen.'],
                    ['title' => 'Day 8: Rotterdam', 'body' => 'Tour the Markthal, the cube houses and the harbor by boat.'],
                    ['title' => 'Day 9: Free day', 'body' => 'A free day in Amsterdam, with a farewell dinner in the evening.'],
                    ['title' => 'Day 10: Departure', 'body' => 'Breakfast and transfer to Schiphol Airport.'],
                ],
            ],
            [
                'slug' => 'windmills-waterways-small-group-tour', 'title' => 'Windmills & Waterways Small-Group Tour', 'type' => 'coach-tours', 'dest' => 'zeeland-and-the-delta', 'nights' => 7, 'price' => 1790,
                'summary' => 'Seven nights among windmills, polders and the Delta Works with a friendly small group.',
                'photo_alts' => ['Windmills along a quiet canal at Kinderdijk', 'Traditional windmills at Zaanse Schans', 'A windmill beside a still canal'],
                'description' => '<p>See how the Dutch built their country. This small-group tour follows the waterways, from the windmills of Kinderdijk to the storm surge barrier in Zeeland, with a guide who explains it all.</p><p>You will also visit charming towns, taste regional food and enjoy time on the coast.</p>',
                'highlights' => ['Kinderdijk UNESCO windmills', 'Zaanse Schans windmill village', 'Delta Works and storm surge barrier', 'Historic Middelburg and Veere', 'Welcome and farewell dinners'],
                'itinerary' => [
                    ['title' => 'Day 1: Arrival in Amsterdam', 'body' => 'Meet your guide and group, then enjoy a welcome dinner.'],
                    ['title' => 'Day 2: Zaanse Schans', 'body' => 'See the windmills at work and learn how they powered Dutch industry.'],
                    ['title' => 'Day 3: Amsterdam', 'body' => 'A morning city tour and canal cruise, with the afternoon free.'],
                    ['title' => 'Day 4: Kinderdijk and Dordrecht', 'body' => 'Tour the Kinderdijk windmills, then explore the historic harbor of Dordrecht.'],
                    ['title' => 'Day 5: Delta Works', 'body' => 'Visit the storm surge barrier and Delta Expo, then continue to your hotel in Middelburg.'],
                    ['title' => 'Day 6: Middelburg and Veere', 'body' => 'Explore the abbey, the town hall and the harbor of Veere.'],
                    ['title' => 'Day 7: Zierikzee and coast', 'body' => 'Stroll the harbor of Zierikzee and relax on the Zeeland coast.'],
                    ['title' => 'Day 8: Departure', 'body' => 'Return to Amsterdam and transfer to Schiphol Airport.'],
                ],
            ],
        ];
    }
}
