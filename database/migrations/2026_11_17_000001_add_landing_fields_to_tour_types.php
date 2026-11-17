<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Each tour type (Self Drive, Chauffeur Driven, Coach Tours) now has its own landing page at /tours/{slug}.
        Schema::table('tour_types', function (Blueprint $t) {
            $t->string('h1')->nullable()->after('slug');
            $t->longText('body')->nullable()->after('summary');
            $t->string('seo_title')->nullable();
            $t->string('meta_description', 320)->nullable();
            $t->boolean('noindex')->default(false);
        });

        // Placeholder copy (never invented facts) so existing sites have something to edit in Admin > Tour types.
        foreach (DB::table('tour_types')->whereNull('body')->get(['id', 'name', 'summary']) as $row) {
            DB::table('tour_types')->where('id', $row->id)->update([
                'body' => '<p>[CONFIRM: '.e($row->name).' page copy. Sample text only.]</p><p>'.e((string) $row->summary).'</p>',
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('tour_types', function (Blueprint $t) {
            $t->dropColumn(['h1', 'body', 'seo_title', 'meta_description', 'noindex']);
        });
    }
};
