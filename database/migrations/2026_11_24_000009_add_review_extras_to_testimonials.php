<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** A star rating, the trip taken and a traveler photo for each review (all optional; existing reviews keep working). */
    public function up(): void
    {
        Schema::table('testimonials', function (Blueprint $t) {
            $t->unsignedTinyInteger('rating')->default(5)->after('quote');
            $t->string('trip')->nullable()->after('location');
            $t->string('image')->nullable()->after('trip');
            $t->string('image_alt')->nullable()->after('image');
        });
    }

    public function down(): void
    {
        Schema::table('testimonials', function (Blueprint $t) {
            $t->dropColumn(['rating', 'trip', 'image', 'image_alt']);
        });
    }
};
