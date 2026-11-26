<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * No more photos of people in suits: the chauffeur page shows a casually dressed couple beside the van, and "Special occasions"
 * shows a canal-side terrace at sunset instead of the dinner party in jackets. Photos changed in the admin are left alone.
 */
return new class extends Migration
{
    public function up(): void
    {
        $swap = [
            'images/tour-chauffeur.webp' => ['images/chauffeur-couple.webp', 'A couple beside a black chauffeur van on an Amsterdam canal quay'],
            'images/special_occasions.webp' => ['images/nh-occasions.webp', 'A glass of sparkling wine on a canal-side terrace in Amsterdam at sunset'],
        ];
        // Replaces the photo wherever a section uses it, together with the alt text stored next to it.
        $walk = function (array &$node) use (&$walk, $swap): bool {
            $changed = false;
            foreach ($node as $key => &$value) {
                if (is_array($value)) {
                    $changed = $walk($value) || $changed;
                } elseif (is_string($value) && isset($swap[$value])) {
                    [$new, $alt] = $swap[$value];
                    $value = $new;
                    foreach ([$key.'_alt', 'alt'] as $altKey) {
                        if (array_key_exists($altKey, $node)) {
                            $node[$altKey] = $alt;
                            break;
                        }
                    }
                    $changed = true;
                }
            }

            return $changed;
        };
        foreach (DB::table('pages')->get(['id', 'sections']) as $page) {
            $sections = json_decode((string) $page->sections, true);
            if (is_array($sections) && $walk($sections)) {
                DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
            }
        }
        foreach ($swap as $old => [$new, $alt]) {
            DB::table('tour_types')->where('image', $old)->update(['image' => $new, 'image_alt' => $alt]);
        }
    }
};
