<?php

namespace App\Support;

use App\Models\BlogPost;

/**
 * The three starter articles (US English) that replace the old "Placeholder article" text. Only well-known, general facts are stated;
 * edit or replace anything in Admin > Blog. Used by the seeder and by the migration that upgrades existing sites.
 */
class BlogContent
{
    public const PLACEHOLDER = '<p>Placeholder article. Replace this text in Admin &gt; Blog with a real guide.</p>';

    /** @return array<string,array<string,string>> keyed by slug */
    public static function all(): array
    {
        return [
            'beyond-amsterdam-five-hidden-gems' => [
                'label' => 'Hidden gems',
                'excerpt' => 'Haarlem, Delft, Utrecht, Veere and Giethoorn: five places that show a quieter side of the Netherlands, and how to enjoy them.',
                'image' => 'images/Guide_1.webp',
                'image_alt' => 'A quiet street of brick townhouses beside a canal in a Dutch town',
                'seo_title' => 'Beyond Amsterdam: 5 Hidden Gems in the Netherlands | GoNetherlandTours',
                'meta_description' => 'Haarlem, Delft, Utrecht, Veere and Giethoorn: five Dutch towns worth the detour, with tips on when to go and how to enjoy them without the crowds.',
                'body' => '<p>Amsterdam deserves its place on every first itinerary, but the Netherlands is small, and some of its best moments are a short ride away. These five places are easy to reach, full of character and much calmer than the capital.</p>'
                    .'<h2>1. Haarlem: a market square with a museum on every corner</h2>'
                    .'<p>Haarlem is a short train ride from Amsterdam and feels like a relaxed version of it. The Grote Markt is ringed by cafes and dominated by the great church of St. Bavo, and the lanes around it hide almshouse courtyards and independent shops. Art lovers should plan time for the Frans Hals Museum.</p>'
                    .'<h2>2. Delft: blue-and-white pottery and canals like a painting</h2>'
                    .'<p>Delft is compact enough to see on foot. Wander the canals, climb a church tower for the view and visit a pottery workshop to see how the famous blue-and-white tiles are painted. It is also the town of the painter Vermeer, which adds an extra layer of interest for museum visitors.</p>'
                    .'<h2>3. Utrecht: a city on two levels</h2>'
                    .'<p>Utrecht\'s old canals sit below street level, with wharf cellars along the water that now hold cafes and restaurants. The Dom Tower is the tallest church tower in the country and a good way to get your bearings. It is lively thanks to its university, yet far less crowded than Amsterdam.</p>'
                    .'<h2>4. Veere: a harbor town in Zeeland</h2>'
                    .'<p>In the south-west, Veere sits on a quiet lake with a tiny harbor, narrow streets and a grand old town hall that looks too pretty to be real. It is a good base for the coast, the dunes and the storm surge barriers of the Delta Works. Read more in our guide to <a href="/destinations/zeeland-and-the-delta">Zeeland and the Delta</a>.</p>'
                    .'<h2>5. Giethoorn: the village without roads</h2>'
                    .'<p>Giethoorn is known for canals instead of streets, with small bridges and thatched farmhouses along the water. Visit early in the morning or late in the day, when the day trippers have gone, and take a quiet electric boat or walk the footpaths.</p>'
                    .'<h2>Tips for visiting</h2>'
                    .'<ul>'
                    .'<li><strong>Go early or late.</strong> Most visitors arrive mid-morning. The first and last hours of the day are the quietest and the light is best for photos.</li>'
                    .'<li><strong>Pair the towns.</strong> Haarlem, Delft and Utrecht fit together easily with a base in a central city, so you spend more time looking and less time driving.</li>'
                    .'<li><strong>Leave some room.</strong> The best stops are often the ones you did not plan, such as a market, a courtyard or a bakery.</li>'
                    .'</ul>'
                    .'<p>If you would like a route that links places like these at an easy pace, take a look at our <a href="/destinations/amsterdam-and-the-cities">Amsterdam and the cities</a> tours, or <a href="/contact">tell us what you have in mind</a> and we will shape a trip around it.</p>',
            ],
            'why-a-small-group-changes-the-way-you-see-the-netherlands' => [
                'label' => 'Small group travel',
                'excerpt' => 'A small group, a local guide and a flexible pace: why this way of traveling suits the Netherlands so well.',
                'image' => 'images/Guide_2.webp',
                'image_alt' => 'The ornate stepped facade of a historic Dutch town hall on a sunny day',
                'seo_title' => 'Why Travel the Netherlands in a Small Group | GoNetherlandTours',
                'meta_description' => 'How a small group tour with a local guide changes what you see in the Netherlands: flexible pace, narrow streets, personal attention and shared costs.',
                'body' => '<p>The Netherlands is full of narrow streets, small museums and family-run places that a big coach simply cannot reach. A small group tour is built for exactly that, and it changes the whole feel of a trip.</p>'
                    .'<h2>Your guide knows your name</h2>'
                    .'<p>Our coach tours are limited to 12 travelers, so your guide can answer questions as they come up, notice who wants more time at a museum and who would rather find a cafe. You are a person, not a seat number.</p>'
                    .'<h2>You reach the places big groups cannot</h2>'
                    .'<p>Small groups fit into small spaces: an almshouse courtyard, a cheese farm, a tiny harbor, a market stall. Parking and timing are simpler too, which means more of your day is spent seeing things instead of waiting for them.</p>'
                    .'<h2>A pace that adjusts</h2>'
                    .'<p>With a handful of people the plan can flex. If the weather turns, the group can swap an outdoor stop for a museum. If everyone loves a village, there is time to linger. The itinerary is a framework, not a stopwatch.</p>'
                    .'<h2>Stories, not just sights</h2>'
                    .'<p>A good local guide connects what you see: why the land is below sea level, how a canal ring was planned, why bicycles outnumber cars. In a small group those stories become conversations, and you leave with more than photos.</p>'
                    .'<h2>Hassle-free, and good value</h2>'
                    .'<p>Transport, sightseeing and accommodation are taken care of, so there is no driving on unfamiliar roads and no juggling of bookings. Costs are shared across the group, which can make a guided trip more affordable than a private one.</p>'
                    .'<h2>Is a small group right for you?</h2>'
                    .'<ul>'
                    .'<li><strong>Choose a small group</strong> if you enjoy meeting other travelers, want a guide to handle the details and like good value.</li>'
                    .'<li><strong>Choose a <a href="/tours/chauffeur-driven">private chauffeur tour</a></strong> if you want a trip just for your own party, with a local driver-guide.</li>'
                    .'<li><strong>Choose a <a href="/tours/self-drive">self drive</a> trip</strong> if you prefer your own pace and are comfortable behind the wheel.</li>'
                    .'</ul>'
                    .'<p>See how it works on our <a href="/tours/coach-tours">small-group coach tours</a>, or <a href="/contact">ask us</a> which style fits your plans.</p>',
            ],
            'tulip-season-when-and-where-to-go' => [
                'label' => 'Tulip season',
                'excerpt' => 'When the tulips bloom, where to see them and how to enjoy the flower fields and gardens without the crowds.',
                'image' => 'images/fina_cta_bg.webp',
                'image_alt' => 'Rows of red and yellow tulips in bloom in a Dutch flower garden',
                'seo_title' => 'Tulip Season in the Netherlands: When and Where to Go | GoNetherlandTours',
                'meta_description' => 'When do the tulips bloom in the Netherlands, and where should you go? A guide to the best weeks, the bulb region fields and the gardens.',
                'body' => '<p>Few sights say spring like the Dutch tulip fields: stripes of red, yellow, pink and purple stretching to the horizon. A little planning makes the difference between a good visit and a great one.</p>'
                    .'<h2>When do the tulips bloom?</h2>'
                    .'<p>Tulips usually bloom from early April to early May, with the peak often falling in mid-to-late April. The exact timing changes every year with the weather, so a cold spring can push the bloom later and a warm one can bring it earlier. Daffodils and hyacinths come first, in March and early April, so there is color even before the main tulip show.</p>'
                    .'<h2>Where to see them</h2>'
                    .'<ul>'
                    .'<li><strong>The bulb region (Bollenstreek).</strong> The fields between Lisse and the coast are the classic tulip landscape. Quiet country roads and bike paths run beside them.</li>'
                    .'<li><strong>Keukenhof.</strong> The famous spring garden is open for only a couple of months each year and shows millions of bulbs in landscaped displays. It is busy at peak weekends, so aim for a weekday and an early start.</li>'
                    .'<li><strong>Windmills and villages.</strong> Pair the flowers with windmills, such as those at Kinderdijk or the Zaanse Schans, for the full postcard image.</li>'
                    .'</ul>'
                    .'<h2>How to enjoy the fields</h2>'
                    .'<ul>'
                    .'<li><strong>Respect the farms.</strong> The fields are working farms. Photograph from the path and never step into the rows.</li>'
                    .'<li><strong>Go early.</strong> The light is soft in the morning and the roads and gardens are quiet.</li>'
                    .'<li><strong>Be flexible.</strong> Because the bloom shifts from year to year, it helps to keep a day or two free to follow the best fields.</li>'
                    .'<li><strong>Dress for changeable weather.</strong> Spring can bring sunshine and showers in the same hour.</li>'
                    .'</ul>'
                    .'<h2>Make it a trip</h2>'
                    .'<p>Our <a href="/destinations/tulips-and-countryside">tulips and countryside</a> tours are timed around the typical bloom, with hotels booked and the route planned for you, and adjusted as the season unfolds. You can see them on a <a href="/tours/self-drive">self drive</a> route, with a private driver-guide or in a small group. <a href="/contact">Tell us your dates</a> and we will suggest the best plan.</p>',
            ],
        ];
    }

    /** Fills in articles that still hold the old placeholder text; anything edited in the admin is left alone. */
    public static function apply(): int
    {
        $n = 0;
        foreach (self::all() as $slug => $c) {
            $post = BlogPost::where('slug', $slug)->first();
            if (! $post) {
                continue;
            }
            $isPlaceholder = trim((string) $post->body) === self::PLACEHOLDER || str_starts_with((string) $post->excerpt, 'Placeholder summary');
            if (! $isPlaceholder) {
                continue;
            }
            $post->update($c);
            $n++;
        }

        return $n;
    }
}
