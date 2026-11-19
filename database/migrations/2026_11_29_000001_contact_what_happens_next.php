<?php

use App\Support\DefaultSections;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Adds a "What happens next" steps section under the Contact form. Only a page still on the default layout is changed. */
    public function up(): void
    {
        $page = DB::table('pages')->where('slug', 'contact')->first();
        if (! $page) {
            return;
        }
        $sections = json_decode((string) $page->sections, true) ?: [];
        if (array_column($sections, 'type') === ['page_header', 'contact_details']) {
            $sections[] = DefaultSections::contactSteps();
            DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
        }
    }
};
