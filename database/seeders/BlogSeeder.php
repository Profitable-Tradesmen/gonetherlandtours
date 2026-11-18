<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use Illuminate\Database\Seeder;

/** Starter articles that replace the old hard-coded "Guides and inspiration" cards. Placeholder copy: edit or replace in Admin > Blog. */
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
        ];

        foreach ($posts as $i => $p) {
            BlogPost::updateOrCreate(['slug' => $p['slug']], $p + [
                'body' => '<p>Placeholder article. Replace this text in Admin &gt; Blog with a real guide.</p>',
                'is_published' => true,
                'published_at' => now()->subDays($i + 1)->toDateString(),
            ]);
        }
    }
}
