<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Home hero (with the quiz): the destination chips return as a sliding row along the bottom of the hero. */
return new class extends Migration
{
    public function up(): void
    {
        $page = DB::table('pages')->where('slug', 'home')->first(['id', 'sections']);
        $sections = $page ? json_decode((string) $page->sections, true) : null;
        if (! is_array($sections)) {
            return;
        }
        foreach ($sections as &$s) {
            if (($s['type'] ?? '') === 'hero_video_quiz') {
                $s['data']['chips_label'] = 'Where to?';
                $s['data']['chips'] = [
                    ['label' => 'Amsterdam & the Cities', 'url' => '/destinations/amsterdam-and-the-cities'],
                    ['label' => 'Tulips & Countryside', 'url' => '/destinations/tulips-and-countryside'],
                    ['label' => 'Zeeland & the Delta', 'url' => '/destinations/zeeland-and-the-delta'],
                    ['label' => 'Self Drive', 'url' => '/tours/self-drive'],
                    ['label' => 'Chauffeur Driven', 'url' => '/tours/chauffeur-driven'],
                    ['label' => 'Coach Tours', 'url' => '/tours/coach-tours'],
                    ['label' => 'All Packages', 'url' => '/packages'],
                ];
            }
        }
        unset($s);
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
