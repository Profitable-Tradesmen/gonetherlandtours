<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The tours of a travel style are now ticked in Admin > Travel styles and stored as links to the packages (they were typed as URL
 * slugs, which broke when a package slug changed). Existing choices are carried over in the same order.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('package_travel_style')) {
            Schema::create('package_travel_style', function (Blueprint $t) {
                $t->foreignId('travel_style_id')->constrained()->cascadeOnDelete();
                $t->foreignId('package_id')->constrained()->cascadeOnDelete();
                $t->unsignedInteger('sort_order')->default(0);
                $t->primary(['travel_style_id', 'package_id']);
            });
        }
        if (! Schema::hasColumn('travel_styles', 'tour_slugs')) {
            return;
        }
        $packages = DB::table('packages')->pluck('id', 'slug');
        foreach (DB::table('travel_styles')->get(['id', 'tour_slugs']) as $style) {
            $order = 0;
            foreach (array_unique(array_filter(array_map('trim', preg_split('/\R/', (string) $style->tour_slugs)))) as $slug) {
                if (isset($packages[$slug])) {
                    DB::table('package_travel_style')->insertOrIgnore(['travel_style_id' => $style->id, 'package_id' => $packages[$slug], 'sort_order' => ++$order]);
                }
            }
        }
        Schema::table('travel_styles', fn (Blueprint $t) => $t->dropColumn('tour_slugs'));
    }

    public function down(): void
    {
        if (! Schema::hasColumn('travel_styles', 'tour_slugs')) {
            Schema::table('travel_styles', fn (Blueprint $t) => $t->text('tour_slugs')->nullable());
        }
        Schema::dropIfExists('package_travel_style');
    }
};
