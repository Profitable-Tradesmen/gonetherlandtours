<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Home, following the sister Go Austria site: a slim contact bar under the hero (phone + quote button), and a travel
 * journal (latest three blog posts) before the closing banner. Theme, colors and fonts are unchanged.
 */
return new class extends Migration
{
    public function up(): void
    {
        $page = DB::table('pages')->where('slug', 'home')->first(['id', 'sections']);
        $sections = $page ? json_decode((string) $page->sections, true) : null;
        if (! is_array($sections)) {
            return;
        }
        $types = array_column($sections, 'type');
        $out = [];
        foreach ($sections as $s) {
            $type = $s['type'] ?? '';
            if ($type === 'cta_banner' && ! in_array('blog_latest', $types, true)) {
                $out[] = ['id' => 'b'.substr(md5('home-journal'), 0, 10), 'type' => 'blog_latest', 'visible' => true, 'data' => [
                    'eyebrow' => 'Travel journal', 'heading' => 'Stories and *tips*', 'intro' => 'Ideas, advice and planning help for your Netherlands trip.',
                    'count' => 3, 'button_label' => 'Read all articles',
                ]];
            }
            $out[] = $s;
            if ($type === 'hero_video_quiz' && ! in_array('contact_bar', $types, true)) {
                $out[] = ['id' => 'c'.substr(md5('home-contact-bar'), 0, 10), 'type' => 'contact_bar', 'visible' => true, 'data' => [
                    'text' => 'Talk to a Netherlands travel specialist', 'button_label' => 'Request a quote',
                ]];
            }
        }
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
