<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Adds the Go Rail Travel logo (already in public/images/partners) to the home Travel partners section, unless it is already there. */
    public function up(): void
    {
        $page = DB::table('pages')->where('slug', 'home')->first();
        $sections = $page ? json_decode((string) $page->sections, true) : null;
        if (! is_array($sections)) {
            return;
        }
        $changed = false;
        foreach ($sections as &$s) {
            if (($s['type'] ?? '') !== 'partners') {
                continue;
            }
            $paths = array_column($s['data']['items'] ?? [], 'image');
            if (! in_array('images/partners/go-rail-travel.png', $paths, true)) {
                $s['data']['items'][] = ['image' => 'images/partners/go-rail-travel.png', 'image_alt' => 'Go Rail Travel', 'url' => ''];
                $changed = true;
            }
        }
        unset($s);
        if ($changed) {
            DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
        }
    }
};
