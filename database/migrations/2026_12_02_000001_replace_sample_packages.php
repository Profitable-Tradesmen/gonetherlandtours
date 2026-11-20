<?php

use App\Support\SamplePackages;
use Illuminate\Database\Migrations\Migration;

/** Replaces the six empty "Sample:" packages with nine complete ones (three per tour type). */
return new class extends Migration
{
    public function up(): void
    {
        SamplePackages::seed();
    }

    public function down(): void {}
};
