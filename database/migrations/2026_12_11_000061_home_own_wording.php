<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Home page: our own headings, labels and button text (nothing copied word for word from the sister Go Austria site). */
return new class extends Migration
{
    public function up(): void
    {
        $page = DB::table('pages')->where('slug', 'home')->first(['id', 'sections']);
        $s = $page ? json_decode((string) $page->sections, true) : null;
        if (! is_array($s)) {
            return;
        }
        $set = [
            'split_list' => ['eyebrow' => 'Why choose us'],
            'packages_tabs' => ['eyebrow' => 'Our signature tours', 'heading' => 'Tours our travelers *love most*', 'intro' => 'Tulip fields, canal cities and the Dutch coast, planned around your dates and your pace.', 'all_label' => 'All tours', 'button_label' => 'Browse all tours'],
            'tour_types' => ['eyebrow' => 'How you travel', 'heading' => 'Self drive, chauffeur or coach: *you choose*'],
            'destination_cards' => ['eyebrow' => 'Where to base your trip', 'heading' => 'Find your corner of *the Netherlands*'],
            'image_strip' => ['eyebrow' => 'Made for you', 'heading' => 'Built around *who you travel with*'],
            'gallery' => ['eyebrow' => 'Picture it', 'heading' => 'Postcards from *the Netherlands*', 'button_label' => 'Get my quote'],
        ];
        foreach ($s as &$x) {
            $t = $x['type'] ?? '';
            if (isset($set[$t])) {
                $x['data'] = array_merge($x['data'] ?? [], $set[$t]);
            }
            if ($t === 'text_cta' && str_starts_with((string) ($x['data']['heading'] ?? ''), 'Tell us your dream')) {
                $x['data']['eyebrow'] = 'Start here';
                $x['data']['heading'] = 'Have a trip in mind? *Let’s shape it together.*';
                $x['data']['text'] = 'Tell us when you want to go and how you like to travel. A Netherlands specialist will reply with a tailored itinerary and a free quote.';
                $x['data']['buttons'] = [['label' => 'Start planning', 'url' => ''], ['label' => 'Browse all tours', 'url' => '/packages']];
            }
            if ($t === 'photo_band' && ($x['data']['button_label'] ?? '') === 'Plan my journey') {
                $x['data']['button_label'] = 'Start planning';
            }
        }
        unset($x);
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($s, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
