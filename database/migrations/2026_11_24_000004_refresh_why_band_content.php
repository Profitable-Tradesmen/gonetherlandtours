<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** [old title, old text, icon, new title, new text]. Only reasons that still have exactly the old default wording are replaced. */
    private const REASONS = [
        ['Authentic Dutch culture', 'Windmills, tulip fields, cheese markets and local traditions.', 'tulip', 'Real Dutch culture', 'Cheese markets, windmills, tulip fields and living local traditions, not just a checklist of sights.'],
        ['Small-group & private tours', 'Personal attention and flexible itineraries, with your own party or a small group.', 'guide', 'Private or small group', 'Self drive and chauffeur tours are just your own party. Coach tours are small guided groups.'],
        ['Hidden gems & local secrets', 'Lesser-known places beyond the tourist hotspots.', 'search', 'Hidden gems, local secrets', 'Quiet canal towns and lesser-known corners beyond the usual tourist hotspots.'],
        ['Expertly crafted itineraries', 'Thoughtfully designed routes and carefully selected attractions.', 'map', 'Itineraries built around you', 'Routes shaped by your dates, pace and interests, with hotels and driving distances planned for you.'],
    ];

    public function up(): void
    {
        foreach (DB::table('pages')->whereNotNull('sections')->get(['id', 'sections']) as $page) {
            $sections = json_decode((string) $page->sections, true);
            if (! is_array($sections)) {
                continue;
            }
            $changed = false;
            foreach ($sections as &$s) {
                if (($s['type'] ?? '') !== 'why_band') {
                    continue;
                }
                foreach ($s['data']['items'] ?? [] as $i => $item) {
                    foreach (self::REASONS as [$oldTitle, $oldText, $icon, $newTitle, $newText]) {
                        if (($item['title'] ?? '') === $oldTitle && ($item['text'] ?? '') === $oldText) {
                            $s['data']['items'][$i] = ['icon' => $icon, 'title' => $newTitle, 'text' => $newText];
                            $changed = true;
                        }
                    }
                }
            }
            unset($s);
            if ($changed) {
                DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
            }
        }
    }

    public function down(): void
    {
        // Content change only: nothing to undo.
    }
};
