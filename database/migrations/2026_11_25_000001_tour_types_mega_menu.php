<?php

use App\Support\DefaultSections;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** The previous seeded header menu: one plain link per tour type. */
    private const OLD_LABELS = ['Home', 'Self Drive', 'Chauffeur Driven', 'Coach Tours', 'Destinations', 'About Us'];

    private const OLD_KINDS = ['link', 'link', 'link', 'link', 'link', 'cards'];

    /**
     * Switches the header to Home, Tour Types (mega menu), Destinations, Packages, About Us, but ONLY while the menu is still exactly the
     * one the previous release seeded; a menu an admin edited is left alone. Also updates the wording of the About dropdown intro panel
     * when it is still the old default text.
     */
    public function up(): void
    {
        $top = DB::table('menu_items')->where('menu', 'header')->whereNull('parent_id')->orderBy('sort_order')->get(['label', 'kind']);
        if ($top->pluck('label')->all() === self::OLD_LABELS && $top->pluck('kind')->all() === self::OLD_KINDS) {
            DefaultSections::resetHeaderMenu();
        }

        $replace = [
            'nav_promo_title' => ['Journeys planned around you.', 'Plan with *peace of mind*'],
            'nav_promo_text' => ['Tell us how you like to travel and we will shape the trip.', 'Learn about our team, read what travelers say and find the answers that make your Netherlands trip smooth and stress-free.'],
        ];
        foreach ($replace as $key => [$old, $new]) {
            DB::table('settings')->where('key', $key)->where('value', $old)->update(['value' => $new]);
        }
    }

    public function down(): void
    {
        // Content change only: nothing to undo.
    }
};
