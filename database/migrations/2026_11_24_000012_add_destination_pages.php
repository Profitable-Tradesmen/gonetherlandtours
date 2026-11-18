<?php

use App\Support\DestinationPages;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /** Creates the section-built page of each destination that does not have one yet (the guide text stays editable in Admin > Destinations). */
    public function up(): void
    {
        DestinationPages::ensureAll();
    }

    public function down(): void
    {
        // Content only: nothing to undo.
    }
};
