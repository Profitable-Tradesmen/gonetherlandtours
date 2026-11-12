<?php

namespace Database\Seeders;

use App\Models\QuizStep;
use Illuminate\Database\Seeder;

/**
 * Hero quiz question steps (the final contact step is the shared enquiry form, variant "quiz").
 * Editable in Admin > Quiz. The pop-up form also reads its dropdown options (travel style, travellers,
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
                ['Autumn', 'Autumn', 'autumn'], ['Winter', 'Winter', 'winter'],
            ]],
            ['travel_style', 'What is your favourite way to see a new country?', 'tiles', [
                ['By coach with other travellers', 'Coach tour', 'coach'],
                ['Driving yourself', 'Self drive', 'car'],
                ['With a tour guide driving', 'Chauffeur driven', 'guide'],
                ["Don't know", "Don't know", 'question'],
            ]],
            ['group_size', 'Number of travellers', 'select', array_merge($range(1, 9), [['10+', '10+', null]])],
            // type "text": free text answer, saved as the enquiry's preferred travel date
            ['travel_date', 'Preferred travel dates', 'text', []],
            ['nights', 'How many nights do you want to stay for?', 'select', array_merge($range(3, 13), [['14+', '14+', null]])],
            ['flights_booked', 'Have you flights booked?', 'tiles', [
                ['Yes', 'Yes', 'yes'], ['No', 'No', 'no'],
            ]],
            ['budget', 'Have you a budget in mind for your trip?', 'tiles', [
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
