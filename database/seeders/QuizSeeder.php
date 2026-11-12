<?php

namespace Database\Seeders;

use App\Models\QuizStep;
use Illuminate\Database\Seeder;

/** Default 8 question steps (the 9th step, contact details, is the shared enquiry form). Editable in Admin > Quiz. */
class QuizSeeder extends Seeder
{
    public function run(): void
    {
        if (QuizStep::count()) {
            return;
        }

        $range = fn (int $from, int $to, string $suffix = '') => collect(range($from, $to))->map(fn ($n) => [(string) $n.$suffix, (string) $n.$suffix, null])->all();

        $steps = [
            ['region', 'Would you like to visit Amsterdam & the cities or the Countryside & tulips?', 'tiles', [
                ['Amsterdam & cities', 'Amsterdam & cities', 'city'],
                ['Countryside & tulips', 'Countryside & tulips', 'tulip'],
            ]],
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
            ['group_size', 'How many people are in your group?', 'select', array_merge($range(1, 9), [['10+', '10+', null]])],
            ['nights', 'How many nights?', 'select', array_merge($range(3, 13), [['14+', '14+', null]])],
            ['flights_booked', 'Have you booked flights?', 'tiles', [
                ['Yes', 'Yes', 'yes'], ['No', 'No', 'no'],
            ]],
            ['budget', 'Have you a budget in mind for your trip?', 'tiles', [
                ['$1K per person', '$1K per person', 'money'], ['$2K per person', '$2K per person', 'money'],
                ['$3K per person', '$3K per person', 'money'], ['$4K+ per person', '$4K+ per person', 'money'],
            ]],
            ['likelihood', 'How likely are you to come?', 'tiles', [
                ["I'm 100% sure I'm going", '100% sure', 'check'], ["I'm 80% sure", '80% sure', 'check'],
                ["It's 50/50", '50/50', 'question'], ["Unsure, just researching", 'Just researching', 'search'],
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
