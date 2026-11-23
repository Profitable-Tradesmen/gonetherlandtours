<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Per-enquiry status of the Google Sheets sync (Admin > Google Sheets): off | pending | synced | failed. */
    public function up(): void
    {
        Schema::table('enquiries', function (Blueprint $t) {
            $t->string('sheets_status', 20)->default('off')->index();
            $t->text('sheets_error')->nullable();
            $t->timestamp('sheets_synced_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('enquiries', function (Blueprint $t) {
            $t->dropColumn(['sheets_status', 'sheets_error', 'sheets_synced_at']);
        });
    }
};
