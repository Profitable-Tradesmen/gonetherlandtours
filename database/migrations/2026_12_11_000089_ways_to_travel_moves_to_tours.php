<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** The tour types overview moved from /ways-to-travel to /tours (the old address redirects); menu links follow. */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('menu_items')->where('url', '/ways-to-travel')->update(['url' => '/tours', 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('menu_items')->where('url', '/tours')->update(['url' => '/ways-to-travel', 'updated_at' => now()]);
    }
};
