<?php

use App\Support\ConfirmCleanup;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;

/** Replaces every "[CONFIRM: ...]" marker in the stored content with finished wording. */
return new class extends Migration
{
    public function up(): void
    {
        ConfirmCleanup::run();
        Cache::flush();
    }

    public function down(): void {}
};
