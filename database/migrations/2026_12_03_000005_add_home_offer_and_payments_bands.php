<?php

use App\Support\DefaultSections;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Adds an offer band after the packages and a "simple, secure payments" band after "How it works" on the home page (once). */
return new class extends Migration
{
    public function up(): void
    {
        $page = DB::table('pages')->where('slug', 'home')->first(['id', 'sections']);
        $sections = $page ? json_decode((string) $page->sections, true) : null;
        if (! is_array($sections)) {
            return;
        }
        $has = fn (string $needle) => collect($sections)->contains(fn ($s) => ($s['type'] ?? '') === 'promo_band' && str_contains((string) ($s['data']['heading'] ?? ''), $needle));
        $insertAfter = function (array $sections, string $type, array $new) {
            foreach ($sections as $i => $s) {
                if (($s['type'] ?? '') === $type) {
                    array_splice($sections, $i + 1, 0, [$new]);

                    return $sections;
                }
            }

            return $sections;
        };
        if (! $has('current offers')) {
            $sections = $insertAfter($sections, 'packages_tabs', DefaultSections::offerBand());
        }
        if (! $has('secure')) {
            $sections = $insertAfter($sections, 'steps', DefaultSections::paymentsBand());
        }
        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode(array_values($sections), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]);
    }

    public function down(): void {}
};
