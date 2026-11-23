<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The "Plan with peace of mind" text band and the FAQ list are no longer part of the home page (the FAQ page, the About Us
     * dropdown and the footer still link to /faq). On the home page an untouched band (still its default heading) is removed;
     * one an admin edited is only hidden, so the work is not lost. Other pages are not touched.
     */
    public function up(): void
    {
        $home = DB::table('pages')->where('slug', 'home')->first();
        $sections = $home ? json_decode((string) $home->sections, true) : null;
        if (! is_array($sections)) {
            return;
        }

        $defaults = ['text_cta' => 'Plan with *peace of mind*', 'faq' => 'Frequently Asked Questions'];
        $changed = false;
        foreach ($sections as $i => $s) {
            $type = $s['type'] ?? '';
            if (! isset($defaults[$type])) {
                continue;
            }
            if (($s['data']['heading'] ?? $defaults[$type]) === $defaults[$type]) {
                unset($sections[$i]);
            } else {
                $sections[$i]['visible'] = false;
            }
            $changed = true;
        }
        if ($changed) {
            DB::table('pages')->where('id', $home->id)->update(['sections' => json_encode(array_values($sections), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
        }
    }

    public function down(): void
    {
        // Layout change only: nothing to undo.
    }
};
