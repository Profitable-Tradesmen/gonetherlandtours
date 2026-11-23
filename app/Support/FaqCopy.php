<?php

namespace App\Support;

/** The default FAQs, written for travelers from the US and Canada. Used by the seeder and by the "US and Canada copy" migration. */
class FaqCopy
{
    /** @return array<int,array{0:string,1:string,2:?string}> [question, answer, question it replaces (for existing sites), or null for a new FAQ] */
    public static function all(): array
    {
        return [
            ['When is the best time to visit the Netherlands?', 'Spring is popular for tulip season and summer for long, bright days. Fall and winter are quieter. Tulips usually bloom from late March to mid May, with the peak in mid April.', null],
            ['Do I need a visa to visit the Netherlands from the US or Canada?', 'Citizens of the US and Canada can generally visit the Netherlands without a visa for short tourist stays of up to 90 days in any 180-day period, with a passport that meets Schengen requirements (generally valid for at least three months after you leave). The EU is also introducing an ETIAS travel authorization for visa-exempt visitors. Entry rules change, so please check the official Dutch government website and your own government\'s travel advice (the US State Department or the Government of Canada) before you travel.', 'Do I need a visa to visit the Netherlands from the US?'],
            ['Can I drive in the Netherlands with a US or Canadian license?', 'Visitors from the US and Canada can generally drive on their valid home license for short stays, and we confirm the requirements for your license with your quote. Rental cars in Europe are usually manual, so ask for an automatic when you request your quote. Distances and speed limits are in kilometers.', 'Can I drive in the Netherlands with a US license?'],
            ['Which airport do I fly into?', 'Most guests fly into Amsterdam Schiphol (AMS), which has frequent flights from many US and Canadian cities. Tell us your flight details when you ask for a quote and we will plan your arrival, pickup or car collection around them.', null],
            ['What currency are your prices in?', 'Prices are shown in euros (EUR), because that is what hotels, drivers and attractions are paid in. Your card issuer sets the exchange rate and may add foreign transaction fees, so it is worth checking with them before you pay.', null],
            ['What is included in a package?', 'Each package lists exactly what is included and excluded on its page.', null],
            ['How do I get a quote?', 'Answer a few quick questions or send an inquiry. A travel specialist will come back to you with a tailored quote.', null],
            ['Can you customize a trip for me?', 'Yes. Every itinerary can be adjusted to your dates, group size and interests.', 'Can you tailor a trip for me?'],
            ['Do I need travel insurance?', 'We strongly recommend comprehensive travel insurance that covers medical care, trip cancellation and baggage. Check your health plan too: many US plans and Canadian provincial plans cover little or nothing outside your home country.', null],
        ];
    }
}
