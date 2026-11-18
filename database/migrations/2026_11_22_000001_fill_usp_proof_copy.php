<?php

use App\Support\DefaultSections;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** The original placeholder proofs from the previous release, by item title. */
    private const OLD = [
        'Authentic Dutch Culture' => '[CONFIRM: a real example, such as a named cheese market, craft workshop or local host on your itineraries]',
        'Small-Group & Private Tours' => '[CONFIRM: your maximum group size on coach tours, for example "no more than N travelers"]',
        'Hidden Gems & Local Secrets' => '[CONFIRM: three named lesser-known places that appear on your itineraries]',
        'Expertly Crafted Itineraries' => '[CONFIRM: who designs the itineraries and what experience they have]',
    ];

    /**
     * Fills the "Selling points with proof" section with drafted copy. Only an item whose proof is still exactly the old
     * placeholder is replaced; text an admin already wrote is never touched.
     */
    public function up(): void
    {
        $new = [];
        foreach (DefaultSections::uspProof()['data']['items'] as $item) {
            $new[$item['title']] = $item['proof'];
        }

        foreach (DB::table('pages')->whereNotNull('sections')->get(['id', 'sections']) as $page) {
            $sections = json_decode((string) $page->sections, true);
            if (! is_array($sections)) {
                continue;
            }
            $changed = false;
            foreach ($sections as &$s) {
                if (($s['type'] ?? '') !== 'usp_proof') {
                    continue;
                }
                foreach ($s['data']['items'] ?? [] as $i => $item) {
                    $title = $item['title'] ?? '';
                    if (isset(self::OLD[$title], $new[$title]) && ($item['proof'] ?? '') === self::OLD[$title]) {
                        $s['data']['items'][$i]['proof'] = $new[$title];
                        $changed = true;
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
