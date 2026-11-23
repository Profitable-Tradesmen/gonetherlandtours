<?php

namespace Database\Seeders;

use App\Models\QuizStep;
use Illuminate\Database\Seeder;

/**
 * Hero quiz question steps (the final contact step is the shared enquiry form, variant "quiz").
 * Editable in Admin > Quiz. The pop-up form also reads its dropdown options (travel style, travelers,
 * nights, budget) from these steps, so the two forms stay consistent.
 */
class QuizSeeder extends Seeder
{
    public function run(): void
    {
        if (QuizStep::count()) {
            return;
        }

        $range = fn (int $from, int $to) => collect(range($from, $to))->map(fn ($n) => [(string) $n, (string) $n, null])->all();

        $steps = [
            ['season', 'What time of year do you want to travel?', 'tiles', [
                ['Spring', 'Spring', 'spring'], ['Summer', 'Summer', 'summer'],
                ['Fall', 'Autumn', 'autumn'], ['Winter', 'Winter', 'winter'],
            ]],
            ['travel_style', 'What is your favorite way to see a new country?', 'tiles', [
                ['By coach with other travelers', 'Coach tour', 'coach'],
                ['Driving yourself', 'Self drive', 'car'],
                ['With a tour guide driving', 'Chauffeur driven', 'guide'],
                ["Don't know", "Don't know", 'question'],
            ]],
            ['group_size', 'Number of travelers', 'select', array_merge($range(1, 9), [['10+', '10+', null]])],
            // type "date": calendar picker, saved as the inquiry's preferred travel date
            ['travel_date', 'Preferred travel dates', 'date', []],
            ['nights', 'How many nights would you like to stay?', 'select', array_merge($range(1, 13), [['14+', '14+', null]])],
            ['flights_booked', 'Have you booked your flights?', 'tiles', [
                ['Yes', 'Yes', 'yes'], ['No', 'No', 'no'],
            ]],
            ['budget', 'Do you have a budget in mind for your trip?', 'tiles', [
                ['$1K pp', '$1K pp', 'money'], ['$2K pp', '$2K pp', 'money'], ['$3K pp', '$3K pp', 'money'],
            ]],
            ['likelihood', 'How likely are you to come to the Netherlands?', 'tiles', [
                ["I'm 80% sure I'm going", '80% sure', 'check'],
                ["I'm 50/50", '50/50', 'question'],
                ['Unsure, just researching', 'Just researching', 'search'],
            ]],
        ];

        foreach ($steps as $i => [$key, $question, $type, $options]) {
            $step = QuizStep::create(['key' => $key, 'question' => $question, 'type' => $type, 'sort_order' => $i + 1]);
            foreach ($options as $j => [$label, $value, $icon]) {
                $step->options()->create(['label' => $label, 'value' => $value, 'icon' => $icon, 'sort_order' => $j + 1]);
            }
        }
    }
}
