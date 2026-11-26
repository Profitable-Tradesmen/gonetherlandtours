<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Header menu order: Home, Tour types, Destinations, Travel styles, About us, Contact. Other items added in the admin follow. */
return new class extends Migration
{
    public function up(): void
    {
        $items = DB::table('menu_items')->where('menu', 'header')->whereNull('parent_id')->orderBy('sort_order')->get(['id', 'kind', 'url']);
        $rank = fn ($m) => match (true) {
            $m->url === '/' => 1,
            $m->kind === 'tours' => 2,
            $m->url === '/destinations' => 3,
            $m->kind === 'styles' => 4,
            $m->url === '/about' => 5,
            $m->url === '/contact' => 6,
            default => 7,
        };
        foreach ($items->sortBy($rank)->values() as $i => $m) {
            DB::table('menu_items')->where('id', $m->id)->update(['sort_order' => $i + 1]);
        }
    }
};
