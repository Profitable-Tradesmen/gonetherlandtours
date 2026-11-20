<?php

namespace App\Console\Commands;

use App\Models\Package;
use App\Models\Page;
use App\Models\TourType;
use App\Support\SamplePackages;
use Illuminate\Console\Command;

/**
 * Points the home page and the tour types at the current photos and prints every image path it finds, with whether
 * the file exists. Safe to run repeatedly:  php artisan images:repair   (add --check to report without changing anything).
 */
class RepairImages extends Command
{
    protected $signature = 'images:repair {--check : report only, change nothing}';

    protected $description = 'Set the home hero / intro / tour-type photos to the current files and report any image path that is missing';

    public function handle(): int
    {
        $check = (bool) $this->option('check');
        $exists = fn (string $p) => is_file(public_path($p)) ? 'ok' : 'MISSING';

        $page = Page::where('slug', 'home')->first();
        if ($page) {
            $sections = $page->sections;
            foreach ($sections as $i => $s) {
                $type = $s['type'] ?? '';
                $want = match ($type) {
                    'hero_video_quiz' => ['poster' => 'images/hero-home.webp'],
                    'split_list' => ['image1' => 'images/Intro_image_split.webp', 'image2' => 'images/Intro_image_split1.webp'],
                    default => [],
                };
                foreach ($want as $key => $path) {
                    $now = $s['data'][$key] ?? '(empty)';
                    $this->line(sprintf('home %-16s %-7s now=%s  -> %s [%s]', $type, $key, $now, $path, $exists($path)));
                    if (! $check) {
                        $sections[$i]['data'][$key] = $path;
                    }
                }
            }
            if (! $check) {
                $page->sections = $sections;
                $page->save();
            }
        } else {
            $this->warn('No home page row found.');
        }

        $photos = [
            'self-drive' => ['storage/media/2026/bkhahmxqvg5hpu-960.webp', 'A small car driving along a country road beside Dutch tulip fields and a windmill'],
            'chauffeur-driven' => ['storage/media/2026/si2kwqzr9sahez-960.webp', 'A chauffeur opening the door of a black car on a historic Dutch street'],
            'coach-tours' => ['storage/media/2026/fieujyz6zribuo-960.webp', 'A small tour group walking beside a minibus on a Dutch canal-side street'],
        ];
        foreach ($photos as $slug => [$path, $alt]) {
            $type = TourType::where('slug', $slug)->first();
            if (! $type) {
                continue;
            }
            $this->line(sprintf('tour type %-17s now=%s  -> %s [%s]', $slug, $type->image, $path, $exists($path)));
            if (! $check) {
                $type->update(['image' => $path, 'image_alt' => $alt]);
            }
        }

        if (! $check) {
            SamplePackages::seed();
        }
        foreach (Package::withCount('images')->orderBy('sort_order')->get() as $p) {
            $this->line(sprintf('package %-42s photos=%d', $p->slug, $p->images_count));
        }

        $this->info($check ? 'Check only: nothing changed.' : 'Done. Now run: php artisan optimize:clear');

        return self::SUCCESS;
    }
}
