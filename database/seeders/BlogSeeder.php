<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use Illuminate\Database\Seeder;

/** Starter articles (the text lives in App\Support\BlogContent). Edit or replace them in Admin > Blog. */
class BlogSeeder extends Seeder
{
    public function run(): void
    {
        $posts = [
            [
                'slug' => 'beyond-amsterdam-five-hidden-gems',
                'label' => 'Guide 01',
                'title' => 'Beyond Amsterdam: five hidden gems worth the detour',
                'excerpt' => 'Placeholder summary. Replace with a real guide to lesser-known Dutch towns and local secrets.',
                'image' => 'images/Guide_1.webp',
                'image_alt' => 'A quiet Dutch town square with a church tower in soft morning light',
            ],
            [
                'slug' => 'why-a-small-group-changes-the-way-you-see-the-netherlands',
                'label' => 'Guide 02',
                'title' => 'Why a small group changes the way you see the Netherlands',
                'excerpt' => 'Placeholder summary. Replace with a real guide on flexible itineraries and personal attention.',
                'image' => 'images/Guide_2.webp',
                'image_alt' => 'A guide pointing out a canal view to a small group of travelers',
            ],
            [
                'slug' => 'tulip-season-when-and-where-to-go',
                'label' => 'Guide 03',
                'title' => 'Tulip season: when and where to go',
                'excerpt' => 'Placeholder summary. Replace with a real guide to the best weeks, fields and gardens for tulips.',
                'image' => 'images/off_the_beaten_path.webp',
                'image_alt' => 'A quiet Dutch harbor village with colorful boats in morning mist',
            ],
        ];

        foreach ($posts as $i => $p) {
            BlogPost::updateOrCreate(['slug' => $p['slug']], $p + [
                'body' => \App\Support\BlogContent::PLACEHOLDER,
                'is_published' => true,
                'published_at' => now()->subDays($i + 1)->toDateString(),
            ]);
        }
        \App\Support\BlogContent::apply();   // swaps the starter text for the real articles
    }
}
