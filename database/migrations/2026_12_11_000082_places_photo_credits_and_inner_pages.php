<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Places (shown on tour, tour type and destination pages and on the tour map), photo credits (/photo-credits), a fourth journal
 * article, real photographs for the travel styles and "Special occasions", and the new footer links. Everything is editable in the
 * admin, and photos already changed there are left alone.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('places')) {
            Schema::create('places', function (Blueprint $t) {
                $t->id();
                $t->foreignId('destination_id')->nullable()->constrained()->nullOnDelete();
                $t->string('name', 120)->unique();
                $t->string('note', 500)->nullable();
                $t->decimal('lat', 8, 4)->nullable();
                $t->decimal('lon', 8, 4)->nullable();
                $t->string('image')->nullable();
                $t->string('image_alt', 190)->nullable();
                $t->unsignedInteger('sort_order')->default(0);
                $t->boolean('is_active')->default(true);
                $t->timestamps();
            });
        }
        if (! Schema::hasTable('photo_credits')) {
            Schema::create('photo_credits', function (Blueprint $t) {
                $t->id();
                $t->string('title', 190);
                $t->string('author', 190);
                $t->string('license', 60);
                $t->string('license_url')->nullable();
                $t->string('source_url', 500)->nullable();
                $t->string('file')->nullable();
                $t->unsignedInteger('sort_order')->default(0);
                $t->timestamps();
            });
        }

        $this->places();
        $this->credits();
        $this->photos();
        $this->journal();
        $this->footerLinks();
    }

    public function down(): void
    {
        Schema::dropIfExists('places');
        Schema::dropIfExists('photo_credits');
    }

    private function places(): void
    {
        if (DB::table('places')->exists()) {
            return;
        }
        $region = DB::table('destinations')->pluck('id', 'slug');
        $a = 'amsterdam-and-the-cities';
        $t = 'tulips-and-countryside';
        $z = 'zeeland-and-the-delta';
        // name, region, latitude, longitude, note, photo file, photo description
        $rows = [
            ['Amsterdam', $a, 52.3702, 4.8952, 'Gabled houses along a ring of canals, major art museums and neighborhoods made for wandering.', 'amsterdam', 'A canal bridge in Amsterdam with the Zuiderkerk tower behind it'],
            ['Haarlem', $a, 52.3813, 4.6368, 'A compact old city on the Spaarne river, with a grand market square and hidden courtyards.', 'haarlem', 'Historic buildings and a sailing boat on the Spaarne river in Haarlem'],
            ['Leiden', $a, 52.1601, 4.4970, 'A university town of canals and bridges, quieter than Amsterdam and easy to cover on foot.', 'leiden', 'A stone bridge over a tree-lined canal in Leiden'],
            ['Delft', $a, 52.0116, 4.3571, 'Home of blue-and-white pottery and of the painter Vermeer, gathered around a wide market square.', 'delft', 'The tower of the New Church above the market square in Delft'],
            ['Utrecht', $a, 52.0907, 5.1214, 'A lively city where cafés sit in old wharf cellars at water level, below the Dom Tower.', 'utrecht', 'The Dom Tower rising above the Oudegracht canal in Utrecht'],
            ['The Hague', $a, 52.0799, 4.3113, 'The seat of government, with the Mauritshuis art collection and a beach at Scheveningen.', 'the-hague', 'The Mauritshuis reflected in the Hofvijver pond in The Hague'],
            ['Rotterdam', $a, 51.9244, 4.4777, 'Bold modern architecture beside one of the busiest harbors in Europe.', 'rotterdam', 'The Erasmus Bridge and the Rotterdam skyline at dusk'],
            ['Keukenhof', $t, 52.2697, 4.5462, 'Spring flower gardens near Lisse, usually open from mid-March to mid-May.', 'keukenhof', 'Beds of spring flowers in bloom at the Keukenhof gardens'],
            ['Lisse', $t, 52.2575, 4.5575, 'The heart of the bulb region, where bands of color stretch across the fields in spring.', 'lisse', 'Rows of red tulips in a bulb field near Lisse'],
            ['Zaanse Schans', $t, 52.4742, 4.8173, 'Working windmills and green wooden houses along the river Zaan, a short hop from Amsterdam.', 'zaanse-schans', 'Windmills and wooden buildings along the water at Zaanse Schans'],
            ['Edam', $t, 52.5122, 5.0486, 'A small, calm town of canals and drawbridges that gave its name to a famous cheese.', 'edam', 'A green, tree-lined canal in the town of Edam'],
            ['Volendam', $t, 52.4959, 5.0708, 'A fishing harbor lined with wooden houses, good for fresh fish by the water.', 'volendam', 'Boats and wooden houses along the harbor at Volendam'],
            ['Alkmaar', $t, 52.6324, 4.7534, 'Known for its cheese market on the old weighing square, held on spring and summer Fridays.', 'alkmaar', 'The historic weigh house and market stalls in Alkmaar'],
            ['Gouda', $t, 52.0115, 4.7105, 'A Gothic town hall on a broad market square, and the cheese that carries the town’s name.', 'gouda', 'The Gothic town hall on the market square in Gouda'],
            ['Kinderdijk', $t, 51.8825, 4.6398, 'A row of 19 historic windmills beside the water, listed as a UNESCO World Heritage Site.', 'kinderdijk', 'Windmills along a reed-lined canal at Kinderdijk'],
            ['Dordrecht', $t, 51.8133, 4.6901, 'One of the oldest cities in Holland, with historic harbors under a massive church tower.', 'dordrecht', 'Boats in an old harbor below the Great Church in Dordrecht'],
            ['Giethoorn', $t, 52.7400, 6.0775, 'A village of thatched farmhouses where narrow waterways and footbridges take the place of many streets.', 'giethoorn', 'A quiet waterway between gardens in Giethoorn'],
            ['Middelburg', $z, 51.4988, 3.6110, 'The capital of Zeeland, built around an old abbey and an ornate town hall.', 'middelburg', 'The ornate town hall on the market square in Middelburg'],
            ['Veere', $z, 51.5481, 3.6670, 'A small harbor town with merchant houses and a long trading history.', 'veere', 'Sailing boats in the harbor of Veere with the town hall tower behind'],
            ['Delta Works', $z, 51.6278, 3.6970, 'Huge dams and storm surge barriers that protect the southwest from the North Sea.', 'delta-works', 'The Oosterschelde storm surge barrier stretching across the water'],
            ['Zierikzee', $z, 51.6501, 3.9185, 'Old city gates, quays and monuments in a relaxed town on Schouwen-Duiveland.', 'zierikzee', 'A harbor gate and old houses reflected in the water at Zierikzee'],
            ['Domburg', $z, 51.5636, 3.4961, 'A seaside village with wide sand, dunes and rows of wooden breakwaters.', 'domburg', 'Wooden breakwaters running into the sea on the beach at Domburg'],
        ];
        foreach ($rows as $i => [$name, $slug, $lat, $lon, $note, $file, $alt]) {
            DB::table('places')->insert([
                'destination_id' => $region[$slug] ?? null, 'name' => $name, 'note' => $note, 'lat' => $lat, 'lon' => $lon,
                'image' => "images/places/$file.webp", 'image_alt' => $alt, 'sort_order' => $i + 1, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    private function credits(): void
    {
        if (DB::table('photo_credits')->exists()) {
            return;
        }
        $rows = array_values((array) json_decode((string) file_get_contents(database_path('data/photo_credits.json')), true));
        // The evening canal photo in the home page gallery, added earlier with a Creative Commons license.
        $rows[] = ['title' => 'Amsterdam canal after dark (home page gallery)', 'author' => 'Jorge Franganillo', 'license' => 'CC BY 2.0',
            'license_url' => 'https://creativecommons.org/licenses/by/2.0/', 'source_url' => null, 'file' => 'images/gallery-3.webp'];
        foreach ($rows as $i => $r) {
            DB::table('photo_credits')->insert([
                'title' => $r['title'], 'author' => $r['author'] ?: 'Unknown', 'license' => $r['license'], 'license_url' => $r['license_url'] ?: null,
                'source_url' => $r['source_url'] ?: null, 'file' => $r['file'], 'sort_order' => $i + 1, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    private function photos(): void
    {
        $styles = [
            'tulips-and-gardens' => ['images/nh-cta.webp', 'style-tulips', 'Long rows of tulips in bloom in the Dutch bulb region'],
            'canals-and-culture' => ['images/nh-first-time.webp', 'style-canals', 'A tree-lined Amsterdam canal with boats and historic houses'],
            'windmills-and-waterways' => ['images/video-feature.webp', 'style-windmills', 'Windmills at Kinderdijk reflected in the water at sunset'],
            'cheese-and-local-flavors' => ['images/nh-culture.webp', 'style-cheese', 'Cheese carriers in white uniforms at the Alkmaar cheese market'],
            'cycling-and-countryside' => ['images/families.webp', 'style-cycling', 'A cycle path beside the water with a windmill at Kinderdijk'],
            'coast-and-harbor-towns' => ['images/nh-offpath.webp', 'style-coast', 'Traditional fishing boats moored in the harbor of Urk'],
        ];
        foreach ($styles as $slug => [$old, $file, $alt]) {
            DB::table('travel_styles')->where('slug', $slug)->where('image', $old)
                ->update(['image' => "images/places/$file.webp", 'image_alt' => $alt, 'updated_at' => now()]);
        }

        foreach (DB::table('pages')->get(['id', 'sections']) as $page) {
            $sections = json_decode((string) $page->sections, true);
            if (! is_array($sections)) {
                continue;
            }
            $changed = false;
            foreach ($sections as &$s) {
                foreach ($s['data']['items'] ?? [] as $k => $item) {
                    if (is_array($item) && ($item['image'] ?? '') === 'images/nh-occasions.webp') {
                        $s['data']['items'][$k]['image'] = 'images/places/special-evening.webp';
                        $s['data']['items'][$k][array_key_exists('alt', $item) ? 'alt' : 'image_alt'] = 'The lit Skinny Bridge over the Amstel in Amsterdam at dusk';
                        $changed = true;
                    }
                }
            }
            unset($s);
            if ($changed) {
                DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
            }
        }
    }

    private function journal(): void
    {
        $slug = 'getting-around-the-netherlands-car-driver-or-coach';
        if (DB::table('blog_posts')->where('slug', $slug)->exists()) {
            return;
        }
        $body = '<p>The Netherlands is small and well connected, so the question is rarely whether you can get somewhere. It is how you want the days to feel. Here is how the three ways we travel compare.</p>'
            .'<h2>Self drive: stop where you like</h2><p>A rental car suits travelers who want to linger in a village or leave early for the flower fields. Roads are well signposted and distances are short. Old city centers are the exception: parking is limited and costly, so it often pays to leave the car at a park-and-ride and continue by tram or on foot.</p>'
            .'<h2>Chauffeur driven: your own driver-guide</h2><p>With a private driver-guide, nobody in your party has to watch the road or look for parking. The day can change as you go, and local stories come with the ride. It is a comfortable choice after a long flight, and for families or friends who want to stay together.</p>'
            .'<h2>Small-group coach: company and a set plan</h2><p>A guided coach tour takes care of the route, the timing and the luggage. You share the experience with other travelers and still get free time in the towns. It usually suits first-time visitors and solo travelers well.</p>'
            .'<h2>What about trains and bikes?</h2><p>Trains link the main cities frequently, and cycling is part of daily life, with separate paths in most places. Both can be added to any of the three styles: a train day into a city, or an easy ride through the bulb fields.</p>'
            .'<h2>How to choose</h2><p>Ask yourself three things: who wants to drive, how fixed you like your days to be, and whether you want company beyond your own party. Tell us your answers and we will suggest a route, with a free, no-obligation quote.</p>';
        DB::table('blog_posts')->insert([
            'title' => 'Car, driver or coach: getting around the Netherlands', 'slug' => $slug, 'label' => 'Planning',
            'excerpt' => 'Self drive, private chauffeur or small-group coach? How each one changes your days, and how to pick.',
            'body' => $body, 'image' => 'images/places/journal-getting-around.webp', 'image_alt' => 'A country road lined with tall trees in the Netherlands',
            'meta_description' => 'Compare self drive, chauffeur driven and small-group coach travel in the Netherlands and choose the right way to get around.',
            'noindex' => 0, 'is_published' => 1, 'published_at' => '2026-11-12', 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function footerLinks(): void
    {
        $footer = fn () => DB::table('menu_items')->where('menu', 'footer');
        if (! $footer()->exists()) {
            return;
        }
        foreach (['/ways-to-travel' => 'Ways to travel', '/photo-credits' => 'Photo credits'] as $url => $label) {
            if (! $footer()->where('url', $url)->exists()) {
                DB::table('menu_items')->insert(['menu' => 'footer', 'kind' => 'link', 'label' => $label, 'url' => $url, 'new_tab' => 0, 'is_active' => 1,
                    'sort_order' => (int) $footer()->max('sort_order') + 1, 'created_at' => now(), 'updated_at' => now()]);
            }
        }
    }
};
