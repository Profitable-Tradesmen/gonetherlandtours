<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * The "Home Page New" design becomes the real home page, and /home-page-new is deleted.
 *  - The home page keeps its own title, SEO fields, indexing and system flag; only its sections are replaced.
 *  - The old home sections are saved to storage/app/backups/home-sections-before-new-layout.json first.
 *  - Does nothing if the home page or Home Page New is missing, so it is safe to run on any database.
 */
return new class extends Migration
{
    public function up(): void
    {
        $new = DB::table('pages')->where('slug', 'home-page-new')->first(['id', 'sections']);
        $home = DB::table('pages')->where('slug', 'home')->first(['id', 'sections']);
        if (! $new || ! $home || ! is_array(json_decode((string) $new->sections, true))) {
            return;
        }

        $dir = storage_path('app/backups');
        File::ensureDirectoryExists($dir);
        File::put($dir.'/home-sections-before-new-layout.json', (string) $home->sections);

        DB::table('pages')->where('id', $home->id)->update([
            'sections' => $new->sections,
            'h1' => 'Unforgettable Netherlands Escapes',
            'updated_at' => now(),
        ]);
        DB::table('pages')->where('id', $new->id)->delete();
    }

    public function down(): void
    {
        $file = storage_path('app/backups/home-sections-before-new-layout.json');
        if (is_file($file)) {
            DB::table('pages')->where('slug', 'home')->update(['sections' => File::get($file), 'h1' => 'Discover the Authentic Netherlands']);
        }
    }
};
