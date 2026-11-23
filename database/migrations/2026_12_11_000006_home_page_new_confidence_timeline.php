<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Home Page New: "Travel with confidence" uses the connected numbered timeline design, and the separate "How It Works" section is removed. */
return new class extends Migration
{
    public function up(): void
    {
        $page = DB::table('pages')->where('slug', 'home-page-new')->first(['id', 'sections']);
        $sections = $page ? json_decode((string) $page->sections, true) : null;
        if (! is_array($sections)) {
            return;
        }
        $sections = array_values(array_filter($sections, fn ($s) => ! (($s['type'] ?? '') === 'steps' && ($s['data']['heading'] ?? '') === 'How It Works')));
        foreach ($sections as &$s) {
            if (($s['type'] ?? '') === 'steps' && ($s['data']['eyebrow'] ?? '') === 'Travel with confidence') {
                $s['data']['style'] = 'timeline';
            }
        }
        unset($s);
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
};
