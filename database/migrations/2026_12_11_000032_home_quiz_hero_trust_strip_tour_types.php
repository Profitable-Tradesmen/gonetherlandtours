<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Home page, following the layout of the sister Go Germany site:
 *  1. the centered hero becomes the hero with the quote quiz beside the text (the same pattern as every other page banner),
 *  2. a four-point trust strip sits right under the hero,
 *  3. the three tour types (Self Drive, Chauffeur Driven, Coach) get their own cards before the packages.
 */
return new class extends Migration
{
    public function up(): void
    {
        $page = DB::table('pages')->where('slug', 'home')->first(['id', 'sections']);
        $sections = $page ? json_decode((string) $page->sections, true) : null;
        if (! is_array($sections) || ! in_array('hero_centered', array_column($sections, 'type'), true)) {
            return;
        }
        $out = [];
        foreach ($sections as $s) {
            $type = $s['type'] ?? '';
            if ($type === 'hero_centered') {
                $d = $s['data'];
                $out[] = ['id' => $s['id'], 'type' => 'hero_video_quiz', 'visible' => true, 'data' => [
                    'eyebrow' => $d['eyebrow'] ?? 'Premier Netherlands Tours',
                    'heading' => $d['heading'] ?? 'Unforgettable *Netherlands* Escapes',
                    'subheading' => $d['text'] ?? '',
                    'primary_label' => $d['primary_label'] ?? 'Get a free quote',
                    'secondary_label' => $d['secondary_label'] ?? 'Explore the Netherlands',
                    'secondary_url' => $d['secondary_url'] ?? '/destinations',
                    'badges' => [],
                    'poster' => $d['image'] ?? 'images/hero-home.webp',
                    'show_video' => false,
                ]];
                $out[] = ['id' => 'v'.substr(md5('home-value-strip'), 0, 10), 'type' => 'value_strip', 'visible' => true, 'data' => ['items' => [
                    ['icon' => 'check', 'title' => 'No hidden fees', 'text' => 'What you see is what you pay.'],
                    ['icon' => 'money', 'title' => 'Clear euro pricing', 'text' => 'Know what is included before you decide.'],
                    ['icon' => 'map', 'title' => 'Locally crafted routes', 'text' => 'Built around the places you want to see.'],
                    ['icon' => 'phone', 'title' => 'Real support', 'text' => 'A travel specialist replies to you.'],
                ]]];
                continue;
            }
            if ($type === 'packages_tabs') {
                $out[] = ['id' => 't'.substr(md5('home-tour-types'), 0, 10), 'type' => 'tour_types', 'visible' => true, 'data' => [
                    'eyebrow' => 'Choose your style',
                    'heading' => 'Three ways to *explore* the Netherlands',
                    'quote_label' => 'Get a Quote',
                    'ask_label' => 'Ask Us a Question',
                ]];
            }
            $out[] = $s;
        }
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
