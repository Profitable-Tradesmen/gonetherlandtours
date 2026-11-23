<?php

namespace App\Support;

/**
 * Drafted guide copy for the three seeded destinations (US English). Used by the seeder and by the migration that replaces the old
 * "[CONFIRM: destination guide copy ...]" placeholder. Only well-known, general facts are stated; anything specific to your itineraries is
 * marked [CONFIRM: ...] for you to check.
 */
class DestinationCopy
{
    public const PLACEHOLDER_MARK = '[CONFIRM: destination guide copy for';

    /** @return array<string,array{h1:string,summary:string,image_alt:string,seo_title:string,meta_description:string,body:string}> keyed by destination slug */
    public static function all(): array
    {
        return [
            'amsterdam-and-the-cities' => [
                'h1' => 'Amsterdam and the Dutch Cities',
                'summary' => 'Canals, world-class museums and historic Dutch cities such as Utrecht, Haarlem and Delft.',
                'image_alt' => 'Amsterdam canal houses and a canal boat on a bright morning',
                'seo_title' => 'Amsterdam and the Dutch Cities Tours | GoNetherlandTours',
                'meta_description' => 'Explore Amsterdam, Haarlem, Delft and Utrecht on a self drive, chauffeur driven or small-group coach tour. Canals, museums and historic old towns, planned around you.',
                'body' => '<h2>The Netherlands at its most classic</h2>'
                    .'<p>Gabled canal houses, narrow bridges, bicycles at every corner and a museum around every other turn: the Dutch cities are compact, walkable and full of character. Amsterdam is the obvious starting point, but the smaller historic towns close by are where many travelers say they felt closest to everyday Dutch life.</p>'
                    .'<h2>Highlights</h2>'
                    .'<ul>'
                    .'<li><strong>Amsterdam:</strong> the 17th-century canal ring, the Rijksmuseum and the Van Gogh Museum, and a canal cruise at golden hour.</li>'
                    .'<li><strong>Haarlem:</strong> a relaxed old town with a grand market square, almshouse courtyards and the Frans Hals Museum.</li>'
                    .'<li><strong>Delft:</strong> blue-and-white pottery, a medieval center and canals that feel like a painting.</li>'
                    .'<li><strong>Utrecht:</strong> a city of two levels, with canal-side wharf cellars and the tallest church tower in the country.</li>'
                    .'</ul>'
                    .'<h2>When to go</h2>'
                    .'<p>Spring brings tulips and long, bright days, summer is lively and warm, and fall is quieter with golden light on the water. Popular sights such as the Anne Frank House and the major museums sell out, so we reserve timed entry for you well ahead.</p>'
                    .'<h2>How you can travel</h2>'
                    .'<p>Choose a <a href="/tours/self-drive">self drive</a> trip with hotels booked for you, a private <a href="/tours/chauffeur-driven">chauffeur driven</a> tour with a local driver-guide, or a <a href="/tours/coach-tours">small-group coach tour</a> led by a guide. Cities are easy to explore on foot, so parking and driving distances are planned with care. Typical stops include Amsterdam, Haarlem, Utrecht, Delft and Rotterdam.</p>'
                    .'<p>Tell us your dates and interests and we will build the route around you, with a clear, no-obligation quote.</p>',
            ],
            'tulips-and-countryside' => [
                'h1' => 'Tulips and the Dutch Countryside',
                'summary' => 'Flower fields, windmills and quiet villages in the Dutch countryside.',
                'image_alt' => 'A traditional windmill beside rows of tulips at golden hour',
                'seo_title' => 'Tulip Fields and Dutch Countryside Tours | GoNetherlandTours',
                'meta_description' => 'See tulip fields, windmills, cheese markets and quiet villages on a self drive, chauffeur driven or small-group coach tour of the Dutch countryside.',
                'body' => '<h2>Windmills, flower fields and slow villages</h2>'
                    .'<p>Beyond the cities, the Netherlands opens up into wide skies, straight canals and fields of color. This is the Holland of postcards, and it is even better in person: a windmill turning over a polder, a bicycle on a dike road, a village where the cheese is still sold at a weekly market.</p>'
                    .'<h2>Highlights</h2>'
                    .'<ul>'
                    .'<li><strong>Tulip season:</strong> the fields around the Bollenstreek bloom in bands of red, yellow and pink, usually from early April to early May, and Keukenhof opens its gardens each spring.</li>'
                    .'<li><strong>Windmills:</strong> from the 19 windmills of Kinderdijk to the working mills of the Zaanse Schans.</li>'
                    .'<li><strong>Cheese markets:</strong> Alkmaar (Friday mornings, April to September) and Gouda (Thursday mornings, April to August).</li>'
                    .'<li><strong>Quiet villages:</strong> early-morning lanes and village harbors before the day trippers arrive, such as Giethoorn.</li>'
                    .'</ul>'
                    .'<h2>When to go</h2>'
                    .'<p>Tulip timing changes with the weather each year, so we plan around the typical bloom and adjust as the season unfolds. Spring is the busiest season for visitors from the US and Canada, so it pays to book early. Outside tulip season, summer and early fall bring green fields, open-air markets and long evenings.</p>'
                    .'<h2>How you can travel</h2>'
                    .'<p>A <a href="/tours/self-drive">self drive</a> route lets you stop whenever a field or a village catches your eye. A <a href="/tours/chauffeur-driven">chauffeur driven</a> tour takes the driving and the parking off your hands, and a <a href="/tours/coach-tours">small-group coach tour</a> adds a local guide and good company. Typical stops include the tulip fields, the windmills of Kinderdijk and the canals of Giethoorn.</p>'
                    .'<p>Share when you would like to travel and we will suggest the best match, with a clear, no-obligation quote.</p>',
            ],
            'zeeland-and-the-delta' => [
                'h1' => 'Zeeland and the Delta',
                'summary' => 'Coastline, sea defenses and charming small towns in the south-west.',
                'image_alt' => 'A quiet Dutch harbor village with historic sailing boats in morning mist',
                'seo_title' => 'Zeeland and the Delta Coast Tours | GoNetherlandTours',
                'meta_description' => 'Discover Zeeland: dunes and beaches, the Delta Works, oysters and mussels, and historic towns such as Middelburg, Veere and Zierikzee. Self drive, chauffeur driven or small-group tours.',
                'body' => '<h2>Where the land meets the sea</h2>'
                    .'<p>Zeeland is the Netherlands at its most open and unhurried: islands and peninsulas linked by bridges and dams, long beaches, and small harbor towns where time seems to move more slowly. It is also the heart of the Delta Works, the vast system of storm surge barriers that protects the country from the North Sea.</p>'
                    .'<h2>Highlights</h2>'
                    .'<ul>'
                    .'<li><strong>The Delta Works:</strong> drive or walk across the Oosterschelde storm surge barrier and see engineering on a monumental scale.</li>'
                    .'<li><strong>Historic towns:</strong> Middelburg, Veere and Zierikzee, with old town halls, harbors and quiet cobbled streets.</li>'
                    .'<li><strong>Beaches and dunes:</strong> wide North Sea beaches and walking paths through the dunes.</li>'
                    .'<li><strong>Local flavors:</strong> Zeeland mussels and oysters in season, and coastal cafes with a view.</li>'
                    .'</ul>'
                    .'<h2>When to go</h2>'
                    .'<p>Late spring to early fall is the most comfortable time for the coast, with long days for exploring. The shoulder months are quieter and the light over the water can be beautiful.</p>'
                    .'<h2>How you can travel</h2>'
                    .'<p>The coast is made for a relaxed <a href="/tours/self-drive">self drive</a>, with short distances between towns and plenty of places to stop. A <a href="/tours/chauffeur-driven">chauffeur driven</a> tour adds local stories at each stop, and a <a href="/tours/coach-tours">small-group coach tour</a> can combine Zeeland with other regions. Typical stops include Middelburg, Veere and Zierikzee.</p>'
                    .'<p>Tell us how long you have and what you enjoy most, and we will plan the route, with a clear, no-obligation quote.</p>',
            ],
        ];
    }

    /** Old seeded values that may be replaced when they are still unchanged. */
    public const OLD_SUMMARY_ZEELAND = 'Coastline, sea defences and charming small towns in the south-west.';

    public const OLD_ALT = [
        'amsterdam-and-the-cities' => 'Amsterdam canal houses reflected in the water',
        'tulips-and-countryside' => 'A windmill beside a Dutch canal',
        'zeeland-and-the-delta' => 'Dutch coastline with dunes',
    ];
}
