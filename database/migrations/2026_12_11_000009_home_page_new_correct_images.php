<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Home Page New: every photo now matches what its text says.
 *  - "Why travel with us" split: the big photo was a bicycle (alt said a quiet canal town) and the small one a Haarlem windmill (alt said
 *    tulips). Now a quiet canal-side street of gabled brick houses, and a close-up of tulips.
 *  - Closing banner: the alt text described a windmill and sunset that are not in the tulip-field photo.
 */
return new class extends Migration
{
    public function up(): void
    {
        $page = DB::table('pages')->where('slug', 'home-page-new')->first(['id', 'sections']);
        $sections = $page ? json_decode((string) $page->sections, true) : null;
        if (! is_array($sections)) {
            return;
        }
        foreach ($sections as &$s) {
            $d = &$s['data'];
            switch ($s['type'] ?? '') {
                case 'split_list':
                    $d['image1'] = 'images/Guide_1.webp';
                    $d['image1_alt'] = 'A quiet Dutch street of gabled brick houses beside a canal';
                    $d['image2'] = 'images/intro-a.webp';
                    $d['image2_alt'] = 'Close-up of pink and purple tulips in bloom';
                    break;
                case 'cta_banner':
                    if (($d['image'] ?? '') === 'images/fina_cta_bg.webp') {
                        $d['image_alt'] = 'Rows of red and yellow tulips in full bloom in a Dutch flower garden';
                    }
                    break;
            }
            unset($d);
        }
        unset($s);
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
