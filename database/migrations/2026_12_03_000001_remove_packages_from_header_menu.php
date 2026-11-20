<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** The /packages page stays; only its top-level link in the header menu is removed. */
    public function up(): void
    {
        DB::table('menu_items')->where('menu', 'header')->whereNull('parent_id')->whereIn('url', ['/packages', 'packages'])->delete();
    }

    public function down(): void
    {
    }
};
