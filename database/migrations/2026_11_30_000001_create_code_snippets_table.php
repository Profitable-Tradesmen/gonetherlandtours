<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Admin > Header & Footer: custom code snippets (scripts, styles, tracking pixels) output on chosen pages at a chosen place. */
    public function up(): void
    {
        Schema::create('code_snippets', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->longText('code');
            $t->string('position', 20)->default('head');   // head | body_start | body_end
            $t->string('scope', 10)->default('all');        // all | only
            $t->text('include_paths')->nullable();           // one path per line (used when scope = only)
            $t->text('exclude_paths')->nullable();           // one path per line (never output here)
            $t->boolean('is_active')->default(true);
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('code_snippets');
    }
};
