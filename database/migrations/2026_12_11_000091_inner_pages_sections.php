<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Inner pages: Terms and Privacy get a simple title bar instead of the quote quiz; Meet the team, Testimonials and FAQ get the
 * planning steps and a closing banner (copied from sections that already exist on the Contact and Team pages).
 */
return new class extends Migration
{
    public function up(): void
    {
        $get = function (string $slug) {
            $p = DB::table('pages')->where('slug', $slug)->first(['id', 'sections']);
            $s = $p ? json_decode((string) $p->sections, true) : null;

            return is_array($s) ? [$p->id, $s] : [null, null];
        };
        $save = fn ($id, $s) => DB::table('pages')->where('id', $id)->update(['sections' => json_encode(array_values($s), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
        $first = function (string $slug, string $type) use ($get) {
            [, $s] = $get($slug);

            return collect($s)->firstWhere('type', $type);
        };

        foreach (['terms', 'privacy'] as $slug) {
            [$id, $sections] = $get($slug);
            if (! $id) {
                continue;
            }
            foreach ($sections as &$x) {
                if (($x['type'] ?? '') === 'page_header') {
                    $x['data']['show_quiz'] = false;
                    $x['data']['tone'] = 'blue';
                }
            }
            unset($x);
            $save($id, $sections);
        }

        $steps = $first('contact', 'steps');
        $banner = $first('meet-the-team', 'cta_banner');
        $add = ['meet-the-team' => [$steps], 'testimonials' => [$steps, $banner], 'faq' => [$banner]];
        foreach ($add as $slug => $blocks) {
            [$id, $sections] = $get($slug);
            if (! $id) {
                continue;
            }
            foreach (array_filter($blocks) as $block) {
                if (in_array($block['type'], array_column($sections, 'type'), true)) {
                    continue;
                }
                $block['id'] = substr(md5($slug.$block['type']), 0, 8);
                $block['visible'] = true;
                // A closing banner stays last; the steps go right before it.
                $at = array_search('cta_banner', array_column($sections, 'type'), true);
                array_splice($sections, $block['type'] === 'cta_banner' || $at === false ? count($sections) : $at, 0, [$block]);
            }
            $save($id, $sections);
        }
    }
};
