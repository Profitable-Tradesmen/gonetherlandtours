<?php

use App\Models\BlogPost;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** A third starter article, and the home "Guides and inspiration" section shows 3 posts (only if it was still on the default of 2). */
    public function up(): void
    {
        BlogPost::firstOrCreate(['slug' => 'tulip-season-when-and-where-to-go'], [
            'label' => 'Guide 03',
            'title' => 'Tulip season: when and where to go',
            'excerpt' => 'Placeholder summary. Replace with a real guide to the best weeks, fields and gardens for tulips.',
            'image' => 'images/off_the_beaten_path.webp',
            'image_alt' => 'A quiet Dutch harbor village with colorful boats in morning mist',
            'body' => '<p>Placeholder article. Replace this text in Admin &gt; Blog with a real guide.</p>',
            'is_published' => true,
            'published_at' => '2026-11-13',   // older than Guides 01 and 02, so the order reads Guide 01, 02, 03
        ]);

        $page = DB::table('pages')->where('slug', 'home')->first();
        $sections = $page ? json_decode((string) $page->sections, true) : null;
        if (! is_array($sections)) {
            return;
        }
        $changed = false;
        foreach ($sections as &$s) {
            if (($s['type'] ?? '') === 'blog_latest' && (int) ($s['data']['count'] ?? 2) === 2) {
                $s['data']['count'] = 3;
                $changed = true;
            }
        }
        unset($s);
        if ($changed) {
            DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
        }
    }
};
