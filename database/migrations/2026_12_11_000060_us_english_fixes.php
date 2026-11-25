<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** US English: "travelers", "harbor" in photo descriptions and "inquiry/inquiries" in the Terms page. */
return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('media')->where('alt', 'like', '%travellers%')->orWhere('alt', 'like', '%harbour%')->get(['id', 'alt']) as $m) {
            DB::table('media')->where('id', $m->id)->update(['alt' => str_replace(['travellers', 'Travellers', 'harbour', 'Harbour'], ['travelers', 'Travelers', 'harbor', 'Harbor'], $m->alt)]);
        }
        foreach (DB::table('pages')->where('body', 'like', '%nquir%')->get(['id', 'body']) as $p) {
            DB::table('pages')->where('id', $p->id)->update(['body' => str_replace(['enquiries', 'Enquiries', 'enquiry', 'Enquiry'], ['inquiries', 'Inquiries', 'inquiry', 'Inquiry'], $p->body)]);
        }
    }
};
