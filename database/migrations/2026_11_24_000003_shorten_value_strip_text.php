<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** The four short points under the hero were shortened so they line up. Only texts that are still exactly the old defaults change. */
    private const OLD = [
        ['Authentic Dutch culture', 'Real traditions and local places, not a checklist.', 'Real Dutch culture', 'Local traditions, not a checklist.'],
        ['Small groups and private tours', 'Your own party, or a small group with a guide who knows your name.', 'Private or small group', 'Your own party, or a small guided group.'],
        ['Real people to help', 'Personal support from first inquiry to your return home.', 'Real people to help', 'Help from first inquiry to return home.'],
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
                if (($s['type'] ?? '') !== 'value_strip') {
                    continue;
                }
                foreach ($s['data']['items'] ?? [] as $i => $item) {
                    foreach (self::OLD as [$oldTitle, $oldText, $newTitle, $newText]) {
                        if (($item['title'] ?? '') === $oldTitle && ($item['text'] ?? '') === $oldText) {
                            $s['data']['items'][$i]['title'] = $newTitle;
                            $s['data']['items'][$i]['text'] = $newText;
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
        // Wording only: nothing to undo.
    }
};
