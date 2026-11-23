<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** The "Get Instant Quote" link in the top bar is removed (a saved blank hides it). Only the old default text is cleared. */
    public function up(): void
    {
        DB::table('settings')->where('key', 'topbar_link_label')->where('value', 'Get Instant Quote')->update(['value' => '']);
    }
};
