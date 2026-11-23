<?php

namespace App\Support;

/**
 * Copy written for travelers flying in from the US and Canada. MAP is "old text => new text", applied to pieces of text inside stored page
 * content (so a page the team has already reworded is left alone) and to the default content in the source files.
 */
class NorthAmericaCopy
{
    public const MAP = [
        // Home hero
        'Windmills, tulip fields and hidden gems on private self drive, chauffeur and small-group coach tours. Answer a few quick questions for a free quote.'
            => 'Fly nonstop from many US and Canadian cities to windmills, tulip fields and hidden gems, on private self drive and chauffeur tours or small-group coach tours. Answer a few quick questions for a free quote.',
        // Home / about steps and CTAs
        'Share when you want to travel, who is coming and what you hope to see.'
            => 'Share when you want to travel, who is coming, what you hope to see and where you are flying from.',
        'Get a free, no-obligation quote for a small-group or custom Netherlands trip.'
            => 'Get a free, no-obligation quote for a small-group or custom Netherlands trip, planned around your flights from the US or Canada.',
        'Tell us your travel plans and preferences.'
            => 'Tell us your dates, your group and where you are flying from.',
        // About
        'Whether it is your first visit or a return trip to see the places you missed, we build something that fits.'
            => 'Whether it is your first visit or a return trip to see the places you missed, we build something that fits. We plan around travelers flying in from the US and Canada, so your first day after the overnight flight is a gentle one.',
        // Contact / FAQ / team
        'Tell us about your trip and a travel specialist will get back to you.'
            => 'Tell us about your trip and a travel specialist will get back to you, wherever you are in the US or Canada.',
        'Send your dates, group size and the kind of trip you have in mind.'
            => 'Send your dates, group size, home airport and the kind of trip you have in mind.',
        'Quick answers about planning a Netherlands trip with us.'
            => 'Quick answers for US and Canadian travelers planning a Netherlands trip with us.',
        // Tour type pages
        'Day by day routes with driving times and suggested stops, so you are never guessing.'
            => 'Day by day routes with driving times and suggested stops, plus tips for drivers used to North American roads, so you are never guessing.',
        'No parking, no navigation and no stress, just time to enjoy the view.'
            => 'No parking, no navigation, no unfamiliar roads and no stress after the long flight, just time to enjoy the view.',
        // Destination pages
        'Outside tulip season,'
            => 'Spring is the busiest season for visitors from the US and Canada, so it pays to book early. Outside tulip season,',
    ];

    public const TICKER = 'Easy Flights from the US & Canada';

    public static function apply(string $text): string
    {
        return strtr($text, self::MAP);
    }
}
