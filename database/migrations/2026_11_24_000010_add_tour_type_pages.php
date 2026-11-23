<?php

use App\Support\TourTypePages;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /** Creates the section-built page of each tour type (Self Drive, Chauffeur Driven, Coach Tours ...) that does not have one yet. */
    public function up(): void
    {
        TourTypePages::ensureAll();
    }

    public function down(): void
    {
        // Content only: nothing to undo.
    }
};
