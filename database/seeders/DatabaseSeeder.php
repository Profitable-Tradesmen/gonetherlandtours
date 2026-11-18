<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            SettingsSeeder::class,
            PagesSeeder::class,
            CatalogSeeder::class,
            QuizSeeder::class,
            BlogSeeder::class,
        ]);
        \App\Support\TourTypePages::ensureAll();   // the Self Drive / Chauffeur Driven / Coach Tours page sections
    }
}
