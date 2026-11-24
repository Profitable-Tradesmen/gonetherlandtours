<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * "Travel your way" (home page image strip): new photos for Couples, Families, Culture lovers and Special occasions
 * (the files in public/images are replaced); this updates their alt text.
 */
return new class extends Migration
{
    public function up(): void
    {
        $alts = [
            'images/couples.webp' => 'A couple relaxing on a small boat on an Amsterdam canal at sunset, with a bottle of wine and brick canal houses behind',
            'images/families.webp' => 'A family of four cycling along a canal path toward the Kinderdijk windmills',
            'images/culture_lovers.webp' => 'A craftsperson hand-painting a blue pattern on a Delft pottery plate in a workshop',
            'images/special_occasions.webp' => 'A candle-lit celebration dinner with a cake and sparkling wine beside a canal-view window',
        ];
        foreach (DB::table('pages')->get(['id', 'sections']) as $page) {
            $sections = json_decode((string) $page->sections, true);
            if (! is_array($sections)) {
                continue;
            }
            $changed = false;
            foreach ($sections as &$s) {
                if (($s['type'] ?? '') !== 'image_strip') {
                    continue;
                }
                foreach ($s['data']['items'] ?? [] as $i => $item) {
                    if (isset($alts[$item['image'] ?? ''])) {
                        $s['data']['items'][$i]['image_alt'] = $alts[$item['image']];
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
};
