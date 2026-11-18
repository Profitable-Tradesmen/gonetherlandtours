<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Header menu: the "Blog" link moves into the "About Us" dropdown as a card.
     * Only acts when the standard layout is still in place (a top-level header link to /blog and an About Us dropdown);
     * a menu an admin has already rearranged is left alone.
     */
    public function up(): void
    {
        $blog = DB::table('menu_items')->where('menu', 'header')->whereNull('parent_id')->where('url', '/blog')->where('kind', 'link')->first();
        $about = DB::table('menu_items')->where('menu', 'header')->whereNull('parent_id')->where('kind', 'cards')->orderBy('sort_order')->first();
        if (! $blog || ! $about) {
            return;
        }

        $last = (int) DB::table('menu_items')->where('parent_id', $about->id)->max('sort_order');
        DB::table('menu_items')->where('id', $blog->id)->update([
            'parent_id' => $about->id,
            'description' => 'Travel guides and inspiration',
            'image' => 'images/Guide_2.webp',
            'image_alt' => 'A Dutch canal-side street with historic gabled houses',
            'sort_order' => $last + 1,
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('menu_items')->where('menu', 'header')->whereNotNull('parent_id')->where('url', '/blog')->update([
            'parent_id' => null, 'description' => null, 'image' => null, 'image_alt' => null, 'updated_at' => now(),
        ]);
    }
};
