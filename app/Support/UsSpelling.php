<?php

namespace App\Support;

/**
 * Converts visitor-facing UK spellings to US spelling (the site targets US travelers).
 * HTML-aware: only text between tags is changed, never tag attributes (links, image paths), and strings that look like URLs or
 * paths are left alone. "enquiry" becomes "inquiry" only in plain phrases ("your enquiry"), never in identifiers.
 */
class UsSpelling
{
    public const WORDS = [
        'travellers' => 'travelers', 'traveller' => 'traveler', 'travelling' => 'traveling', 'travelled' => 'traveled',
        'colourful' => 'colorful', 'coloured' => 'colored', 'colours' => 'colors', 'colour' => 'color',
        'favourites' => 'favorites', 'favourite' => 'favorite',
        'organised' => 'organized', 'organise' => 'organize', 'organisation' => 'organization',
        'cosy' => 'cozy', 'centred' => 'centered', 'centres' => 'centers', 'centre' => 'center',
        'licence' => 'license', 'catalogue' => 'catalog',
        'customised' => 'customized', 'customise' => 'customize', 'customising' => 'customizing', 'customisation' => 'customization', 'customisable' => 'customizable',
        'personalised' => 'personalized', 'personalise' => 'personalize', 'personalising' => 'personalizing', 'personalisation' => 'personalization',
        'specialised' => 'specialized', 'specialise' => 'specialize', 'specialising' => 'specializing',
        'recognised' => 'recognized', 'recognise' => 'recognize', 'realised' => 'realized', 'realise' => 'realize',
        'prioritise' => 'prioritize', 'summarise' => 'summarize', 'emphasise' => 'emphasize', 'apologise' => 'apologize',
        'organisations' => 'organizations', 'organisers' => 'organizers', 'organiser' => 'organizer',
        'jewellery' => 'jewelry', 'cheque' => 'check', 'honour' => 'honor',
        'neighbourhoods' => 'neighborhoods', 'neighbourhood' => 'neighborhood', 'neighbours' => 'neighbors', 'neighbour' => 'neighbor',
        'whilst' => 'while', 'amongst' => 'among', 'cancelled' => 'canceled', 'cancelling' => 'canceling',
        'holidays' => 'vacations', 'holiday' => 'vacation',
    ];

    /** UK phrasing with a different US/Canadian equivalent (regex => replacement; a leading capital is kept). Also used by the "US and Canada" copy pass. */
    public const PHRASES = [
        '/\btailor-made (?=to\b)/i' => 'tailored ',
        '/\btailor-made (?=for\b)/i' => 'tailored ',
        '/\btailor-made\b/i' => 'custom',
        '/\bcar hire\b/i' => 'car rental',
        '/(?<![\w-])autumn(?![\w-])/i' => 'fall',
        '/\bAmerican (travelers)\b/i' => 'American and Canadian $1',
        '/\bUS (travelers)\b/i' => 'US and Canadian $1',
        '/\bPrices in EUR\b/' => 'Prices in euros',
        '/\bHave you flights booked\?/i' => 'Have you booked your flights?',
        '/\bHave you a budget in mind for your trip\?/i' => 'Do you have a budget in mind for your trip?',
        '/\bHow many nights do you want to stay for\?/i' => 'How many nights would you like to stay?',
    ];

    public static function convert(string $text): string
    {
        if ($text === '' || preg_match('#^(https?://|/|images/|storage/|mailto:|tel:|\#)#', $text)) {
            return $text;
        }
        if (str_contains($text, '<')) {
            $parts = preg_split('/(<[^>]*>)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
            foreach ($parts as $i => $part) {
                if ($i % 2 === 0) {
                    $parts[$i] = self::words($part);
                }
            }

            return implode('', $parts);
        }

        return self::words($text);
    }

    /** Walk arrays/JSON structures and convert string values (keys that hold links, paths or identifiers are skipped). */
    public static function deep(mixed $value, ?string $key = null): mixed
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = self::deep($v, is_string($k) ? $k : $key);
            }

            return $value;
        }
        if (! is_string($value) || ($key !== null && (in_array($key, ['id', 'type', 'icon', 'variant', 'style', 'align', 'tone', 'link', 'image', 'slug', 'key', 'value', 'kind', 'url'], true) || str_ends_with($key, '_url') || str_ends_with($key, '_image') || $key === 'poster'))) {
            return $value;
        }

        return self::convert($value);
    }

    private static function words(string $text): string
    {
        foreach (self::WORDS as $uk => $us) {
            $text = preg_replace_callback('/\b'.$uk.'\b/i', function ($m) use ($us) {
                $w = $m[0];
                if (strlen($w) > 1 && $w === strtoupper($w)) {
                    return strtoupper($us);
                }

                return ctype_upper($w[0]) ? ucfirst($us) : $us;
            }, $text);
        }

        foreach (self::PHRASES as $re => $to) {
            $text = preg_replace_callback($re, function ($m) use ($re, $to) {
                $out = preg_replace_callback('/\$(\d)/', fn ($x) => $m[(int) $x[1]] ?? '', $to);

                return ctype_upper($m[0][0]) ? ucfirst($out) : $out;
            }, $text);
        }

        return preg_replace_callback('/\b(your|my|an|the|a|this|each|every|any|send|sending|submit|submitting|new)(\s+)enquir(y|ies)\b/i', fn ($m) => $m[1].$m[2].'inquir'.$m[3], $text);
    }
}
