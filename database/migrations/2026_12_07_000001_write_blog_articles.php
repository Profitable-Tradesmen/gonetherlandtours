<?php

use App\Support\BlogContent;
use Illuminate\Database\Migrations\Migration;

/**
 * Replaces the three "Placeholder article" posts with real starter articles (and matching photos, labels and search titles).
 * Only posts that still hold the placeholder text are changed, so articles edited in the admin are left alone.
 */
return new class extends Migration
{
    public function up(): void
    {
        BlogContent::apply();
    }

    public function down(): void {}
};
