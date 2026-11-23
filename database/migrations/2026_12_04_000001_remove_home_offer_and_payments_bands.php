<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Takes the "current offers" and "secure payments" bands off the home page (they were added by the earlier 2026_12_03_000005 migration). */
return new class extends Migration
{
    public function up(): void
    {
        $page = DB::table('pages')->where('slug', 'home')->first(['id', 'sections']);
        $sections = $page ? json_decode((string) $page->sections, true) : null;
        if (! is_array($sections)) {
            return;
        }
        $kept = array_values(array_filter($sections, function ($s) {
            $h = (string) ($s['data']['heading'] ?? '');

            return ! (($s['type'] ?? '') === 'promo_band' && (str_contains($h, 'current offers') || str_contains($h, 'secure *payments')));
        }));
        if (count($kept) !== count($sections)) {
            DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($kept, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]);
        }
    }

    public function down(): void {}
};
