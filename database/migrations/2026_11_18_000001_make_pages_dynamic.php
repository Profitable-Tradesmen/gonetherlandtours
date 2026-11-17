<?php

use App\Support\DefaultSections;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pages become an ordered list of sections edited in Admin > Pages; menus move into the database.
        Schema::table('pages', function (Blueprint $t) {
            $t->json('sections')->nullable()->after('content');
            $t->boolean('is_published')->default(true)->after('noindex');
            $t->boolean('is_system')->default(false)->after('is_published');
        });

        Schema::create('menu_items', function (Blueprint $t) {
            $t->id();
            $t->string('menu', 20)->index();                 // header | footer
            $t->unsignedBigInteger('parent_id')->nullable()->index();
            $t->string('kind', 12)->default('link');         // link | tours (auto menu of tour types) | cards (dropdown of cards)
            $t->string('label');
            $t->string('description')->nullable();
            $t->string('url')->nullable();                   // /path, https://..., #anchor
            $t->string('image')->nullable();
            $t->string('image_alt')->nullable();
            $t->boolean('new_tab')->default(false);
            $t->unsignedInteger('sort_order')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        // Turn the hard-coded pages and menus into editable data (keeps text admins already changed).
        DefaultSections::fill();
        DefaultSections::menus();
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
        Schema::table('pages', function (Blueprint $t) {
            $t->dropColumn(['sections', 'is_published', 'is_system']);
        });
    }
};
