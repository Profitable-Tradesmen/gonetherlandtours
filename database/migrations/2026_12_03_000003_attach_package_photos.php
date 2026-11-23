<?php

use App\Support\SamplePackages;
use Illuminate\Database\Migrations\Migration;

/**
 * The package migration attaches photos only if the files already exist when it runs; on a server where the
 * uploads arrived afterwards the packages were left without photos. Running the (idempotent) seed again attaches them.
 */
return new class extends Migration
{
    public function up(): void
    {
        SamplePackages::seed();
    }

    public function down(): void {}
};
