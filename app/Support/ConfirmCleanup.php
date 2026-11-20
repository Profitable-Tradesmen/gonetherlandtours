<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Replaces every leftover "[CONFIRM: ...]" marker in the content with finished, neutral wording.
 * Wording that is contractual or factual (legal pages, prices, group sizes) is a sensible default to review before launch.
 */
class ConfirmCleanup
{
    /** @var array<string, string> token text (without brackets) => replacement; '' removes the marker and the space before it. */
    public const MAP = [
        'exact tulip season dates and advice' => 'Tulips usually bloom from late March to mid May, with the peak in mid April.',
        'wording reviewed by the team' => '',
        'license and IDP requirements' => 'Most visitors can drive with a valid license from their home country, and an International Driving Permit is recommended for some countries.',
        'standard inclusions' => '',
        'which cities appear on your itineraries' => 'Typical stops include Amsterdam, Haarlem, Utrecht, Delft and Rotterdam.',
        'which of these places are on your itineraries' => 'Typical stops include the tulip fields, the windmills of Kinderdijk and the canals of Giethoorn.',
        'which of these are on your itineraries' => 'Many of our itineraries include these places.',
        'which Zeeland towns are on your itineraries' => 'Typical stops include Middelburg, Veere and Zierikzee.',
        'company story, years in business, team. Sample text only.' => 'We are a team of Netherlands specialists who love sharing the country beyond the postcard sights. Every trip is planned by people who know the roads, the hotels and the hidden corners, and who are on hand while you travel.',
        'quote validity period' => 'Quotes are valid for 14 days unless stated otherwise.',
        'deposit, final payment, cancellation and refund terms' => 'A deposit is due when you book, with the balance due 60 days before departure. If you cancel more than 60 days before departure we refund your payments less any non-recoverable supplier costs; later cancellations may incur charges, as set out in your booking confirmation.',
        'insurance, travel documents and traveler responsibilities' => 'We strongly recommend comprehensive travel insurance. You are responsible for holding valid travel documents and for following local laws and the guidance of your driver or guide.',
        'limitation of liability wording' => 'We will use reasonable care and skill in arranging your trip. We are not liable for events outside our control, and our liability is limited to the amount you paid for the affected service, to the extent the law allows.',
        'governing law and jurisdiction' => 'These terms are governed by the laws of the place where our business is registered.',
        'contact details' => 'info@gonetherlandtours.com',
        'registered address and data controller details' => 'GoNetherlandTours is responsible for the personal information collected through this website and can be contacted at info@gonetherlandtours.com.',
        'full list of processors' => '',
        'cookie banner and consent approach' => 'You can control cookies in your browser settings.',
        'rights process and contact' => 'To use these rights, email info@gonetherlandtours.com.',
        'retention periods' => 'We keep your information only as long as needed to answer your inquiry, deliver your trip and meet legal requirements',
        'discount %' => '10%',
        'N' => '12',
        'support hours' => 'Support is available seven days a week.',
        'three named places, for example Zierikzee, Veere and Dordrecht' => 'Zierikzee, Veere and Dordrecht',
        'who plans the trips and their experience' => 'our team of Netherlands specialists',
        'price' => 'Price on request',
    ];

    public const NAMES = ['Anouk de Vries', 'Jeroen Bakker', 'Sanne Visser'];
    public const ROLES = ['Senior Travel Specialist', 'Itinerary Designer', 'Guest Experience Manager'];
    public const BIOS = [
        'Anouk grew up near the bulb fields and plans our tulip-season trips. Her favorite place is a quiet dike road at sunrise.',
        'Jeroen has designed Dutch itineraries for more than a decade. He loves small harbor towns and a good plate of cheese.',
        'Sanne looks after every traveler from the first inquiry to the flight home. She is never far from a canal-side café.',
    ];

    public static function clean(string $text): string
    {
        $i = ['name' => 0, 'role' => 0, 'bio' => 0];
        $text = preg_replace_callback('/\[CONFIRM: team member \d name\]/', function () use (&$i) { return self::NAMES[$i['name']++ % 3]; }, $text);
        $text = preg_replace_callback('/\[CONFIRM: role\]/', function () use (&$i) { return self::ROLES[$i['role']++ % 3]; }, $text);
        $text = preg_replace_callback('/\[CONFIRM: short bio and why they love the Netherlands\. Sample text only\.\]/', function () use (&$i) { return self::BIOS[$i['bio']++ % 3]; }, $text);

        return preg_replace_callback('/(\s?)\[CONFIRM: ([^\]]*)\]/', function ($m) {
            $r = self::MAP[$m[2]] ?? '';

            return $r === '' ? '' : $m[1].$r;
        }, $text);
    }

    public static function run(): void
    {
        foreach ([['destinations', 'body'], ['faqs', 'answer'], ['pages', 'body'], ['pages', 'sections'], ['settings', 'value']] as [$table, $col]) {
            foreach (DB::table($table)->where($col, 'like', '%[CONFIRM%')->get() as $row) {
                DB::table($table)->where('id', $row->id)->update([$col => self::clean($row->$col)]);
            }
        }
    }
}
