<?php

namespace App\Services\Ghl;

use App\Models\QuizStep;
use Illuminate\Support\Str;

/**
 * THE single definition of every GoHighLevel custom field we write.
 * Fields are matched in GHL by their EXACT name, so to rename one: change it in GHL and here together.
 * Staff may move fields into folders in GHL, but must not rename them.
 */
class GhlFields
{
    public const CORE = [
        'travel_date' => 'Preferred Travel Date',
        'second_quote' => 'Second Quote Preference',
        'special_request' => 'Special Request',
        'package' => 'Package',
        'form_name' => 'Form Name',
        'quiz_answers' => 'Quiz Answers (Full)',
        'consent_given' => 'Consent Given',
        'consent_at' => 'Consent Timestamp',
        'consent_version' => 'Consent Text Version',
    ];

    /** Tracking keys sent for both first-touch and latest-touch. */
    public const TRACKING = [
        'utm_source' => 'UTM Source',
        'utm_medium' => 'UTM Medium',
        'utm_campaign' => 'UTM Campaign',
        'utm_term' => 'UTM Term',
        'utm_content' => 'UTM Content',
        'gclid' => 'GCLID',
        'gbraid' => 'GBRAID',
        'wbraid' => 'WBRAID',
        'referrer' => 'Referrer',
        'landing_page' => 'Landing Page URL',
    ];

    public static function core(string $key): string
    {
        return self::CORE[$key];
    }

    public static function tracking(string $key, string $touch): string
    {
        return self::TRACKING[$key].' ('.ucfirst($touch).')';   // e.g. "UTM Source (First)"
    }

    public static function quiz(string $stepKey): string
    {
        return 'Quiz: '.Str::headline($stepKey);
    }

    /** @return array<int,string> every field name that must exist in GHL (quiz steps read from the database). */
    public static function all(): array
    {
        $names = array_values(self::CORE);
        foreach (['first', 'last'] as $touch) {
            foreach (array_keys(self::TRACKING) as $k) {
                $names[] = self::tracking($k, $touch);
            }
        }
        $quizKeys = array_unique(array_merge(['region', 'season', 'travel_style', 'group_size', 'nights', 'flights_booked', 'budget', 'likelihood'], QuizStep::pluck('key')->all()));
        foreach ($quizKeys as $k) {
            $names[] = self::quiz($k);
        }

        return array_values(array_unique($names));
    }
}
