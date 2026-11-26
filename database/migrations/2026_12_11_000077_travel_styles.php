<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Travel styles (trips grouped by interest), each with its own page at /travel-styles/slug, plus the "Travel styles" mega menu.
 * Content is edited in Admin > Travel styles.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('travel_styles')) {
            Schema::create('travel_styles', function (Blueprint $t) {
                $t->id();
                $t->string('name', 120);
                $t->string('slug', 140)->unique();
                $t->string('summary', 500)->nullable();
                $t->longText('body')->nullable();
                $t->text('highlights')->nullable();
                $t->string('places')->nullable();
                $t->string('best_time', 190)->nullable();
                $t->text('tour_slugs')->nullable();
                $t->string('image')->nullable();
                $t->string('image_alt', 190)->nullable();
                $t->string('seo_title', 190)->nullable();
                $t->string('meta_description', 320)->nullable();
                $t->boolean('noindex')->default(false);
                $t->unsignedInteger('sort_order')->default(0);
                $t->boolean('is_active')->default(true);
                $t->timestamps();
            });
        }

        if (! DB::table('travel_styles')->exists()) {
            foreach ($this->styles() as $i => $s) {
                DB::table('travel_styles')->insert([
                    'name' => $s['name'], 'slug' => $s['slug'], 'summary' => $s['summary'], 'body' => $s['body'],
                    'highlights' => implode("\n", $s['highlights']), 'places' => $s['places'], 'best_time' => $s['best_time'],
                    'tour_slugs' => implode("\n", $s['tours']), 'image' => $s['image'], 'image_alt' => $s['image_alt'],
                    'seo_title' => $s['name'].' Tours in the Netherlands', 'meta_description' => $s['summary'],
                    'sort_order' => $i + 1, 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }

        // The menu entry sits right after "Tour types"; later items move down one place.
        $header = fn () => DB::table('menu_items')->where('menu', 'header')->whereNull('parent_id');
        if ($header()->exists() && ! $header()->where('kind', 'styles')->exists()) {
            $after = (int) ($header()->where('kind', 'tours')->value('sort_order') ?? 1);
            $header()->where('sort_order', '>', $after)->increment('sort_order');
            DB::table('menu_items')->insert([
                'menu' => 'header', 'kind' => 'styles', 'label' => 'Travel styles', 'url' => '/travel-styles', 'new_tab' => 0, 'is_active' => 1,
                'sort_order' => $after + 1, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('menu_items')->where('kind', 'styles')->delete();
        Schema::dropIfExists('travel_styles');
    }

    private function styles(): array
    {
        return [
            [
                'name' => 'Tulips & Gardens', 'slug' => 'tulips-and-gardens',
                'summary' => 'Spring color in the bulb fields, Keukenhof in bloom and flower markets full of fresh stems.',
                'body' => '<p>For a few weeks each spring the fields between Haarlem and Leiden turn into long stripes of red, yellow and pink. This style is built around that window, with time among the flowers early in the day and slow drives along the lanes that run between them.</p><p>Bloom dates shift with the weather, so we plan around the usual season and stay flexible where we can. Outside tulip time, gardens and flower markets still make a colorful trip.</p>',
                'highlights' => ['A day at the Keukenhof gardens during its spring season', 'The flower route through Lisse and Hillegom', 'Windmills framed by open fields', 'Time at a flower market in the city'],
                'places' => 'Keukenhof, Lisse, Haarlem, Leiden', 'best_time' => 'Mid-March to mid-May; tulips usually peak in April',
                'tours' => ['tulip-route-self-drive', 'netherlands-in-bloom-small-group-tour', 'windmills-tulips-cheese-chauffeur-tour'],
                'image' => 'images/nh-cta.webp', 'image_alt' => 'Rows of red and orange tulips leading to a windmill at sunset',
            ],
            [
                'name' => 'Canals & Culture', 'slug' => 'canals-and-culture',
                'summary' => 'Gabled houses, world-class museums and old towns you can explore on foot.',
                'body' => '<p>Dutch cities are compact and made for walking. Spend a morning with Rembrandt and Vermeer, an afternoon on the water and an evening at a table beside the canal.</p><p>Distances are short, so you can base yourself in one city or link several, from Amsterdam to Utrecht, Delft and The Hague.</p>',
                'highlights' => ['A canal cruise through Amsterdam', 'The Rijksmuseum or the Van Gogh Museum', 'The Mauritshuis in The Hague', 'Blue-and-white pottery in Delft', 'The wharf cellars along Utrecht’s Oudegracht'],
                'places' => 'Amsterdam, Haarlem, Utrecht, Delft, The Hague', 'best_time' => 'All year; spring and early fall are the most comfortable',
                'tours' => ['amsterdam-historic-cities-self-drive', 'cities-canals-small-group-tour', 'utrecht-delft-the-hague-self-drive'],
                'image' => 'images/nh-first-time.webp', 'image_alt' => 'Canal boats passing gabled Amsterdam houses in evening light',
            ],
            [
                'name' => 'Windmills & Waterways', 'slug' => 'windmills-and-waterways',
                'summary' => 'Working windmills, polder landscapes and the clever engineering that keeps the land dry.',
                'body' => '<p>Much of the Netherlands sits at or below sea level, and the story of how it stays dry is written across the landscape. Step inside a working mill at Zaanse Schans, walk the dikes at Kinderdijk and see the storm surge barrier that guards the southwest.</p><p>It suits travelers who like big skies, photography and finding out how things work.</p>',
                'highlights' => ['The 19 windmills of Kinderdijk, a UNESCO World Heritage Site', 'Working mills and wooden houses at Zaanse Schans', 'The Delta Works storm surge barrier', 'A boat ride between the dikes'],
                'places' => 'Zaanse Schans, Kinderdijk, Dordrecht, Zeeland', 'best_time' => 'April to September for long days on the water',
                'tours' => ['windmills-waterways-small-group-tour', 'dutch-highlights-chauffeur-tour', 'tulip-route-self-drive'],
                'image' => 'images/video-feature.webp', 'image_alt' => 'Windmills and farm buildings across a green meadow at Zaanse Schans',
            ],
            [
                'name' => 'Cheese & Local Flavors', 'slug' => 'cheese-and-local-flavors',
                'summary' => 'Cheese markets, farm tastings, fresh seafood and cafés where locals actually eat.',
                'body' => '<p>Taste your way from a farmhouse in the Green Heart to the old weighing square in Alkmaar, then head for the coast for mussels and oysters.</p><p>Market days follow a set calendar, so tell us your dates and we will arrange the route to catch one where possible.</p>',
                'highlights' => ['The Friday cheese market in Alkmaar (spring and summer)', 'Tastings in Gouda and Edam', 'Fresh fish on the harbor in Volendam', 'Zeeland mussels and oysters in season'],
                'places' => 'Alkmaar, Edam, Gouda, Volendam, Yerseke', 'best_time' => 'April to September for the cheese markets',
                'tours' => ['alkmaar-edam-volendam-chauffeur-tour', 'windmills-tulips-cheese-chauffeur-tour', 'zeeland-seaside-historic-towns-small-group-tour'],
                'image' => 'images/nh-culture.webp', 'image_alt' => 'A cheese seller arranging wheels of Dutch cheese at an outdoor market',
            ],
            [
                'name' => 'Cycling & Countryside', 'slug' => 'cycling-and-countryside',
                'summary' => 'Flat, well-marked bike paths past fields, windmills, dunes and quiet villages.',
                'body' => '<p>Cycling is how the Dutch get around, and the paths are mostly flat, signposted and separate from traffic. Add a few easy rides to your trip, from the bulb fields near Lisse to the dunes of Walcheren.</p><p>You choose how much to pedal. Rides can be a short morning loop or the highlight of the day, with the car or driver covering the longer distances.</p>',
                'highlights' => ['Rides through the bulb fields around Lisse', 'Dune paths on the Zeeland coast', 'The dike paths beside Kinderdijk', 'Village stops for coffee and apple pie'],
                'places' => 'Lisse, Kinderdijk, Walcheren, Zierikzee', 'best_time' => 'April to September',
                'tours' => ['tulip-route-self-drive', 'zeeland-coast-self-drive', 'zeeland-seaside-historic-towns-small-group-tour'],
                'image' => 'images/families.webp', 'image_alt' => 'A family cycling along a canal path toward the Kinderdijk windmills',
            ],
            [
                'name' => 'Coast & Harbor Towns', 'slug' => 'coast-and-harbor-towns',
                'summary' => 'Wide beaches, historic harbors and an unhurried pace in Zeeland and beyond.',
                'body' => '<p>In the southwest, islands and peninsulas are joined by bridges and dams. Days here move gently: a walk on the beach, lunch by the harbor and a stroll through a town that once traded with the world.</p><p>It pairs well with a few days in the cities for travelers who want both buzz and calm.</p>',
                'highlights' => ['The harbor town of Veere', 'Middelburg’s abbey and market square', 'Old gates and quays in Zierikzee', 'North Sea beaches and dune walks'],
                'places' => 'Middelburg, Veere, Zierikzee, Domburg', 'best_time' => 'Late May to September',
                'tours' => ['zeeland-coast-self-drive', 'zeeland-delta-chauffeur-tour', 'zeeland-seaside-historic-towns-small-group-tour'],
                'image' => 'images/nh-offpath.webp', 'image_alt' => 'A misty Dutch harbor with historic sailing boats and gabled houses',
            ],
        ];
    }
};
