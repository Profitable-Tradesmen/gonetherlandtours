<?php

use App\Models\Page;
use App\Support\DefaultSections;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * 1. Adds the "Meet the Team" page (a normal custom page, so it is editable and deletable in Admin > Pages) if the slug is free.
     * 2. Switches the header to the reference menu (Home, Self Drive, Chauffeur Driven, Coach Tours, Destinations, About Us dropdown)
     *    ONLY while it is still the menu earlier releases seeded (Tours, [Destinations,] Packages, About Us, Contact). A menu an admin has edited is left alone.
     * 3. Adds a "Meet the team" link to the footer menu when the footer is still the seeded one and the page exists.
     */
    public function up(): void
    {
        if (! DB::table('pages')->where('slug', 'meet-the-team')->exists()) {
            Page::create([
                'slug' => 'meet-the-team',
                'title' => 'Meet the Team',
                'h1' => 'Meet the Team',
                'seo_title' => 'Meet the Team | GoNetherlandTours',
                'meta_description' => 'Meet the travel specialists who plan your Netherlands trip and look after you along the way.',
                'sections' => DefaultSections::teamPage(),
                'is_published' => true,
                'is_system' => false,
            ]);
        }

        $top = DB::table('menu_items')->where('menu', 'header')->whereNull('parent_id')->orderBy('sort_order')->get(['label', 'kind']);
        $labels = $top->pluck('label')->all();
        $kinds = $top->pluck('kind')->all();
        // the seeded menu of earlier releases, with or without the Destinations link that a later migration added
        $seeded = ($labels === ['Tours', 'Packages', 'About Us', 'Contact'] && $kinds === ['tours', 'link', 'cards', 'link'])
            || ($labels === ['Tours', 'Destinations', 'Packages', 'About Us', 'Contact'] && $kinds === ['tours', 'link', 'link', 'cards', 'link']);
        if ($seeded) {
            DefaultSections::resetHeaderMenu();
        }

        $footer = DB::table('menu_items')->where('menu', 'footer')->orderBy('sort_order')->get(['label', 'url']);
        if ($footer->count() === 7 && $footer->firstWhere('url', '/about') && ! $footer->firstWhere('url', '/meet-the-team')) {
            $now = now();
            DB::table('menu_items')->where('menu', 'footer')->where('sort_order', '>', 1)->increment('sort_order');
            DB::table('menu_items')->insert(['menu' => 'footer', 'label' => 'Meet the team', 'url' => '/meet-the-team', 'sort_order' => 2, 'kind' => 'link', 'description' => null, 'image' => null, 'image_alt' => null, 'parent_id' => null, 'is_active' => true, 'new_tab' => false, 'created_at' => $now, 'updated_at' => $now]);
        }
    }

    public function down(): void
    {
        // Content change only: nothing to undo.
    }
};
