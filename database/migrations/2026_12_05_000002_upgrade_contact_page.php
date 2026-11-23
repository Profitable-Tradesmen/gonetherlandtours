<?php

use App\Support\DefaultSections;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Contact page upgrade: a photo behind the title bar, a slim reassurance strip, a few FAQs and reviews. (The specialist card, the
 * call / email / hours cards, the message starters and the links are part of the "Contact details and form" section itself.)
 * Each part is added only if it is missing, and an existing photo or subtitle in the title bar is kept. The order is
 * header, strip, form, steps (sand), FAQ (white), reviews (sand), so neighbouring sections never share a background.
 */
return new class extends Migration
{
    public function up(): void
    {
        $page = DB::table('pages')->where('slug', 'contact')->first(['id', 'sections']);
        $sections = $page ? json_decode((string) $page->sections, true) : null;
        if (! is_array($sections)) {
            return;
        }

        foreach ($sections as &$s) {
            if (($s['type'] ?? '') === 'page_header') {
                $header = DefaultSections::contactHeader()['data'];
                if (trim((string) ($s['data']['image'] ?? '')) === '') {
                    $s['data']['image'] = $header['image'];
                }
                if (trim((string) ($s['data']['subtitle'] ?? '')) === '') {
                    $s['data']['subtitle'] = $header['subtitle'];
                }
                break;
            }
        }
        unset($s);

        $types = fn () => array_map(fn ($s) => $s['type'] ?? '', $sections);

        // a separate "ways to reach us" section from an earlier draft of this change is not wanted
        $sections = array_values(array_filter($sections, fn ($s) => ($s['type'] ?? '') !== 'contact_cards'));

        if (! in_array('feature_cards', $types(), true)) {
            $at = array_search('page_header', $types(), true);
            array_splice($sections, $at === false ? 0 : $at + 1, 0, [DefaultSections::contactStrip()]);
        }
        if (! in_array('faq', $types(), true)) {
            $sections[] = DefaultSections::contactFaq();
        }
        if (! in_array('testimonials', $types(), true)) {
            $sections[] = DefaultSections::contactReviews();
        } else {
            // reviews go after the FAQ: the steps above are on a sand background, so the FAQ (white) must sit between them and the reviews (sand)
            $reviews = array_search('testimonials', $types(), true);
            $faq = array_search('faq', $types(), true);
            if ($reviews !== false && $faq !== false && $reviews < $faq) {
                [$moved] = array_splice($sections, $reviews, 1);
                $sections[] = $moved;
            }
        }

        DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode(array_values($sections), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]);
    }

    public function down(): void {}
};
