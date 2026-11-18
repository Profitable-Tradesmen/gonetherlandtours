<?php

use App\Models\TourType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Adds the small tag and three bullet points shown on the home page "how do you want to travel" cards. */
    public function up(): void
    {
        Schema::table('tour_types', function (Blueprint $t) {
            $t->string('card_tag')->nullable()->after('summary');
            $t->text('card_points')->nullable()->after('card_tag');   // one point per line
        });

        // fill the three seeded tour types, never overwriting anything an admin typed
        foreach (TourType::CARD_DEFAULTS as $slug => [$tag, $points]) {
            TourType::where('slug', $slug)->whereNull('card_tag')->whereNull('card_points')->update(['card_tag' => $tag, 'card_points' => implode("\n", $points)]);
        }
    }

    public function down(): void
    {
        Schema::table('tour_types', function (Blueprint $t) {
            $t->dropColumn(['card_tag', 'card_points']);
        });
    }
};
