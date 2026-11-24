<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Blog posts no longer reuse photos from the home page: "small group" gets the Utrecht canal with a tour boat, "tulip season" gets a close-up of tulips. */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('blog_posts')->where('image', 'images/Guide_2.webp')->update([
            'image' => 'images/trav-2.webp',
            'image_alt' => 'A canal boat gliding past historic houses and terraces on an Utrecht canal',
        ]);
        DB::table('blog_posts')->where('image', 'images/fina_cta_bg.webp')->update([
            'image' => 'images/intro-a.webp',
            'image_alt' => 'Close-up of pink tulips with fresh green leaves',
        ]);
    }
};
