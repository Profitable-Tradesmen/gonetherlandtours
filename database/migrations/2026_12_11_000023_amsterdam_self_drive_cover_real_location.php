<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Amsterdam & Historic Cities Self Drive: new cover (a traveler with a map beside a silver Toyota Yaris on a canal quay, church tower and gabled houses behind). Files are in storage/app/public/media/2026; this updates the alt text. */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('media')->where('path', 'media/2026/pkg-amsterdam-historic-cities-self-drive-1')
            ->update(['alt' => 'A traveler reading a paper map beside a silver hatchback on a canal quay in Amsterdam, with a church tower, gabled brick houses and houseboats behind']);
    }
};
