<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Tulip Route Self Drive: new cover (a Volkswagen Polo on a farm road between tulip fields near Lisse). Files are in storage/app/public/media/2026; this updates the alt text. */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('media')->where('path', 'media/2026/pkg-tulip-route-self-drive-1')
            ->update(['alt' => 'A gray hatchback on a gravel farm road between red, yellow and pink tulip fields, two travelers beside the open door, a farmhouse and a windmill in the distance']);
    }
};
