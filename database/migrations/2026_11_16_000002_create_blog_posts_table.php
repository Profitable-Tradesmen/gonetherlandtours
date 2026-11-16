<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_posts', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->string('slug')->unique();
            $t->string('label')->nullable();            // small eyebrow on cards, e.g. "Guide 01"
            $t->text('excerpt')->nullable();
            $t->longText('body')->nullable();
            $t->string('image')->nullable();
            $t->string('image_alt')->nullable();
            $t->string('seo_title')->nullable();
            $t->string('meta_description', 320)->nullable();
            $t->boolean('noindex')->default(false);
            $t->boolean('is_published')->default(false);
            $t->date('published_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
