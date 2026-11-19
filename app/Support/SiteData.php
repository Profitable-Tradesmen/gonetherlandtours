<?php

namespace App\Support;

use App\Models\Destination;
use App\Models\QuizStep;
use App\Models\TourType;
use Illuminate\Support\Collection;

/**
 * Per-request memo of data that the layout, sections and forms all need (tour types, destinations, quiz steps).
 * Without it the same query ran several times per page (the home page ran 43 queries). Nothing is cached across
 * requests, so admin edits show up immediately.
 */
class SiteData
{
    private static array $memo = [];

    private static function once(string $key, callable $load): mixed
    {
        return self::$memo[$key] ??= $load();
    }

    /** Active tour types, each with its published packages (menus, footer and sections all use this one list). */
    public static function tourTypes(): Collection
    {
        return self::once('tourTypes', fn () => TourType::active()->with(['packages' => fn ($q) => $q->published()->orderBy('title')->with(['images', 'destination'])])->get());
    }

    public static function destinations(): Collection
    {
        return self::once('destinations', fn () => Destination::active()->get());
    }

    /** Active quiz steps with active options, shaped for the Alpine quiz. */
    public static function quizSteps(): array
    {
        return self::once('quizSteps', fn () => QuizStep::forQuiz());
    }

    /** Options (label, value) of one quiz step by key, e.g. "budget". Used by the pop-up form's dropdowns. */
    public static function quizOptions(string $key): Collection
    {
        $step = collect(self::quizSteps())->firstWhere('key', $key);

        return collect($step['options'] ?? [])->map(fn ($o) => (object) ['label' => $o['label'], 'value' => $o['value']]);
    }

    /** Forget the memo (tests and long-running workers). */
    public static function flush(): void
    {
        self::$memo = [];
    }
}
