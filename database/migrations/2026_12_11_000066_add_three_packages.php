<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Three more published packages (one per way to travel, 12 in total), so the home page grids have enough tours. */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $new = [
            [
                'template' => 7, 'slug' => 'utrecht-delft-the-hague-self-drive', 'title' => 'Utrecht, Delft & The Hague Self Drive',
                'tour_type_id' => 1, 'destination_id' => 1, 'nights' => 5, 'price' => 1690, 'sort' => 10, 'media' => [12, 11, 29],
                'summary' => 'Five relaxed nights linking Utrecht, Delft, The Hague and Leiden by car, with canal-side hotels and short, easy drives.',
                'description' => '<p>A self drive for travelers who prefer historic towns to big-city crowds. You follow a short, gentle route from Utrecht through Delft and The Hague to Leiden, with hotels in the old centers so you can walk to dinner.</p><p>Every drive is under an hour, with time built in for museums, markets and long café stops.</p>',
                'highlights' => ['Climb the Dom Tower in Utrecht', 'Blue-and-white pottery workshop in Delft', "The Hague's Mauritshuis and the Peace Palace", 'Canal boat ride in Leiden', 'Seaside dinner at Scheveningen'],
                'days' => [
                    ['Day 1: Arrival and Utrecht', 'Collect your rental car at Schiphol Airport and drive to Utrecht (about 40 minutes). Check in near the Oudegracht and walk the lower wharves along the canal.'],
                    ['Day 2: Utrecht', 'Climb the Dom Tower for views over the old town, then browse the shops and cafés of the Wijde Doelen and Twijnstraat. Optional visit to the Railway Museum.'],
                    ['Day 3: To Delft', 'Drive to Delft, home of Johannes Vermeer and blue-and-white pottery. Visit the Royal Delft factory and the market square, and stay in the old center.'],
                    ['Day 4: The Hague', 'A short drive to The Hague for the Mauritshuis, the Peace Palace and the beach promenade at Scheveningen, with dinner by the sea.'],
                    ['Day 5: Leiden', 'Drive to Leiden, the oldest university town in the country. Take a canal boat, walk the Rapenburg and visit the windmill museum.'],
                    ['Day 6: Departure', 'After breakfast, return to Schiphol Airport (about 30 minutes) to drop off your car and catch your flight home.'],
                ],
            ],
            [
                'template' => 10, 'slug' => 'alkmaar-edam-volendam-chauffeur-tour', 'title' => 'Alkmaar, Edam & Volendam Private Chauffeur Tour',
                'tour_type_id' => 2, 'destination_id' => 2, 'nights' => 4, 'price' => 2190, 'sort' => 11, 'media' => [32, 21, 18],
                'summary' => 'Four nights with your own driver-guide through the cheese towns and fishing villages north of Amsterdam, with no driving for you.',
                'description' => '<p>Let a local driver-guide take you through the cheese markets, wooden harbors and windmill villages of North Holland. You travel in a comfortable private vehicle, with hotels in the old town centers and a pace that suits you.</p><p>Your guide shares the stories behind each stop and handles the roads, parking and tickets.</p>',
                'highlights' => ['Friday cheese market in Alkmaar (in season)', 'Cheese tasting in Edam', 'Fishing harbor of Volendam', 'Zaanse Schans windmills at sunrise', 'Private canal cruise in Amsterdam'],
                'days' => [
                    ['Day 1: Arrival in Amsterdam', 'Your driver meets you at Schiphol Airport and takes you to your hotel in Amsterdam. Evening at leisure.'],
                    ['Day 2: Zaanse Schans and Edam', 'Visit the windmills and wooden houses of Zaanse Schans early, before the crowds, then continue to Edam for a cheese tasting and a walk along the canals.'],
                    ['Day 3: Volendam and Marken', 'Explore the fishing harbor of Volendam and the old island village of Marken, with time for fresh fish at the waterfront.'],
                    ['Day 4: Alkmaar', 'Spend the morning at the cheese market in Alkmaar (April to September) and the afternoon in the old town. Return to Amsterdam for a private canal cruise.'],
                    ['Day 5: Departure', 'Your driver takes you back to Schiphol Airport in time for your flight home.'],
                ],
            ],
            [
                'template' => 13, 'slug' => 'zeeland-seaside-historic-towns-small-group-tour', 'title' => 'Zeeland Seaside & Historic Towns Small-Group Tour',
                'tour_type_id' => 3, 'destination_id' => 3, 'nights' => 6, 'price' => 2390, 'sort' => 12, 'media' => [14, 23, 15],
                'summary' => 'Six nights with a small group and a local guide along the Zeeland coast, from the delta works to the walled harbor towns.',
                'description' => '<p>Join a small group for a slow week on the Dutch coast. A local guide leads you along the dunes and sea walls of Zeeland and through the harbor towns of Middelburg, Veere and Zierikzee, with all transport and hotels arranged.</p><p>The group is limited to 14 travelers, so the pace is easy and the hotels are small and personal.</p>',
                'highlights' => ['Delta Works storm surge barrier', 'Walk the old walls of Veere', 'Middelburg market square and abbey', 'Oyster and mussel tasting at Yerseke', 'Dune walk on the Zeeland coast'],
                'days' => [
                    ['Day 1: Arrival and welcome', 'Meet your guide and the group in Rotterdam and transfer to Middelburg. Welcome dinner in the old town.'],
                    ['Day 2: Middelburg', 'Walk the abbey, the town hall square and the Saturday market. Afternoon at leisure along the canals.'],
                    ['Day 3: Veere and Walcheren', 'Visit the harbor village of Veere and its Scottish houses, then cycle or walk the dunes of Walcheren.'],
                    ['Day 4: The Delta Works', 'Visit the Oosterschelde storm surge barrier and learn how the Dutch hold back the sea. Lunch of local oysters and mussels at Yerseke.'],
                    ['Day 5: Zierikzee', 'Explore the medieval gates and streets of Zierikzee, then take a boat trip on the Oosterschelde.'],
                    ['Day 6: Free day', 'A free day to relax, shop or join an optional bike ride. Farewell dinner together.'],
                    ['Day 7: Departure', 'Transfer to Rotterdam or Amsterdam for your onward journey.'],
                ],
            ],
        ];

        foreach ($new as $n) {
            if (DB::table('packages')->where('slug', $n['slug'])->exists()) {
                continue;
            }
            $t = (array) DB::table('packages')->where('id', $n['template'])->first();
            unset($t['id']);
            $row = array_merge($t, [
                'title' => $n['title'], 'slug' => $n['slug'], 'tour_type_id' => $n['tour_type_id'], 'destination_id' => $n['destination_id'],
                'nights' => $n['nights'], 'price_from' => $n['price'], 'summary' => $n['summary'], 'description' => $n['description'],
                'highlights' => json_encode($n['highlights'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'itinerary' => json_encode(array_map(fn ($d) => ['title' => $d[0], 'body' => $d[1]], $n['days']), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'seo_title' => $n['title'].' | GoNetherlandTours', 'meta_description' => $n['summary'],
                'is_published' => 1, 'is_featured' => 1, 'is_sample' => 0, 'noindex' => 0, 'sort_order' => $n['sort'],
                'created_at' => $now, 'updated_at' => $now,
            ]);
            $id = DB::table('packages')->insertGetId($row);
            foreach ($n['media'] as $i => $m) {
                DB::table('package_images')->insert(['package_id' => $id, 'media_id' => $m, 'sort_order' => $i + 1]);
            }
        }
    }
};
