<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tour cards show the route (main stops, taken from each package's day-by-day plan), destination cards list their places, and on the
 * home page the "great value" tours use large photo cards and the destinations get a "your own route" box. Anything already filled
 * in through the admin is left alone.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('packages', 'route')) {
            Schema::table('packages', fn (Blueprint $t) => $t->string('route', 190)->nullable()->after('summary'));
        }
        if (! Schema::hasColumn('destinations', 'places')) {
            Schema::table('destinations', fn (Blueprint $t) => $t->string('places')->nullable()->after('summary'));
        }

        $routes = [
            'tulip-route-self-drive' => 'Haarlem > Keukenhof > Gouda > Kinderdijk > Leiden',
            'amsterdam-historic-cities-self-drive' => 'Amsterdam > Haarlem > Leiden > Delft > Utrecht',
            'zeeland-coast-self-drive' => 'Middelburg > Veere > Delta Works > Zierikzee > Dordrecht',
            'dutch-highlights-chauffeur-tour' => 'Amsterdam > Zaanse Schans > Haarlem > Delft > Kinderdijk',
            'windmills-tulips-cheese-chauffeur-tour' => 'Amsterdam > Keukenhof > Zaanse Schans > Gouda > Kinderdijk',
            'zeeland-delta-chauffeur-tour' => 'Middelburg > Veere > Delta Works > Zierikzee > Kinderdijk',
            'netherlands-in-bloom-small-group-tour' => 'Amsterdam > Edam > Keukenhof > Gouda > Delft',
            'cities-canals-small-group-tour' => 'Amsterdam > Haarlem > Utrecht > Delft > Rotterdam',
            'windmills-waterways-small-group-tour' => 'Amsterdam > Kinderdijk > Delta Works > Middelburg > Zierikzee',
            'utrecht-delft-the-hague-self-drive' => 'Utrecht > Delft > The Hague > Leiden',
            'alkmaar-edam-volendam-chauffeur-tour' => 'Amsterdam > Edam > Volendam > Alkmaar',
            'zeeland-seaside-historic-towns-small-group-tour' => 'Middelburg > Veere > Delta Works > Zierikzee',
        ];
        foreach ($routes as $slug => $route) {
            DB::table('packages')->where('slug', $slug)->whereNull('route')->update(['route' => $route]);
        }

        $places = [
            'amsterdam-and-the-cities' => 'Amsterdam, Haarlem, Delft, Utrecht, Rotterdam',
            'tulips-and-countryside' => 'Keukenhof, Lisse, Zaanse Schans, Kinderdijk, Giethoorn',
            'zeeland-and-the-delta' => 'Middelburg, Veere, Zierikzee, Domburg',
        ];
        foreach ($places as $slug => $list) {
            DB::table('destinations')->where('slug', $slug)->whereNull('places')->update(['places' => $list]);
        }

        $page = DB::table('pages')->where('slug', 'home')->first(['id', 'sections']);
        $sections = $page ? json_decode((string) $page->sections, true) : null;
        if (! is_array($sections)) {
            return;
        }
        foreach ($sections as &$s) {
            if (($s['type'] ?? '') === 'destination_cards') {
                $s['data'] = array_merge($s['data'] ?? [], [
                    'own_heading' => 'Mix regions your way',
                    'own_text' => 'Can’t pick one corner? Tell us the places on your list and we will join them into a single route.',
                    'own_button' => 'Plan my route',
                ]);
            }
            // The price-sorted grid is the "great value" block near the bottom of the page.
            if (($s['type'] ?? '') === 'tour_packages' && ($s['data']['sort'] ?? '') === 'price') {
                $s['data']['style'] = 'photo';
            }
        }
        unset($s);
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
