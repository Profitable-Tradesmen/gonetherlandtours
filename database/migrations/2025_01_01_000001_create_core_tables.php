<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sessions', function (Blueprint $t) {
            $t->string('id')->primary();
            $t->foreignId('user_id')->nullable()->index();
            $t->string('ip_address', 45)->nullable();
            $t->text('user_agent')->nullable();
            $t->longText('payload');
            $t->integer('last_activity')->index();
        });

        Schema::create('admin_users', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email')->unique();
            $t->string('password');
            $t->rememberToken();
            $t->timestamps();
        });

        Schema::create('settings', function (Blueprint $t) {
            $t->id();
            $t->string('key')->unique();
            $t->longText('value')->nullable();
            $t->timestamps();
        });

        Schema::create('pages', function (Blueprint $t) {
            $t->id();
            $t->string('slug')->unique();           // home, about, contact, terms, privacy
            $t->string('title');
            $t->string('h1')->nullable();
            $t->longText('body')->nullable();       // rich text (about/terms/privacy)
            $t->json('content')->nullable();        // structured section text (home)
            $t->string('seo_title')->nullable();
            $t->string('meta_description', 320)->nullable();
            $t->string('social_image')->nullable();
            $t->boolean('noindex')->default(false);
            $t->string('version', 20)->default('1.0'); // legal version, stored as terms_version
            $t->timestamps();
        });

        Schema::create('tour_types', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('icon')->nullable();
            $t->text('summary')->nullable();
            $t->string('image')->nullable();
            $t->string('image_alt')->nullable();
            $t->unsignedInteger('sort_order')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('destinations', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('h1')->nullable();
            $t->text('summary')->nullable();
            $t->longText('body')->nullable();
            $t->string('image')->nullable();
            $t->string('image_alt')->nullable();
            $t->string('seo_title')->nullable();
            $t->string('meta_description', 320)->nullable();
            $t->boolean('noindex')->default(false);
            $t->unsignedInteger('sort_order')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('packages', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->string('slug')->unique();
            $t->foreignId('tour_type_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('destination_id')->nullable()->constrained()->nullOnDelete();
            $t->unsignedSmallInteger('nights')->default(7);
            $t->decimal('price_from', 10, 2)->nullable();
            $t->string('currency', 3)->default('EUR');
            $t->text('summary')->nullable();
            $t->longText('description')->nullable();
            $t->json('highlights')->nullable();
            $t->json('itinerary')->nullable();     // [{title, body}]
            $t->json('inclusions')->nullable();
            $t->json('exclusions')->nullable();
            $t->json('faqs')->nullable();          // [{question, answer}]
            $t->string('seo_title')->nullable();
            $t->string('meta_description', 320)->nullable();
            $t->string('social_image')->nullable();
            $t->boolean('noindex')->default(false);
            $t->boolean('is_published')->default(false);
            $t->boolean('is_featured')->default(false);
            $t->boolean('is_sample')->default(false);
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
        });

        Schema::create('media', function (Blueprint $t) {
            $t->id();
            $t->string('path');                    // base path, no extension e.g. media/2026/abc
            $t->json('widths');                    // generated widths
            $t->unsignedInteger('width');
            $t->unsignedInteger('height');
            $t->string('alt');
            $t->string('original_name')->nullable();
            $t->timestamps();
        });

        Schema::create('package_images', function (Blueprint $t) {
            $t->id();
            $t->foreignId('package_id')->constrained()->cascadeOnDelete();
            $t->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $t->unsignedInteger('sort_order')->default(0);
        });

        Schema::create('faqs', function (Blueprint $t) {
            $t->id();
            $t->string('question');
            $t->text('answer');
            $t->unsignedInteger('sort_order')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('testimonials', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('location')->nullable();
            $t->text('quote');
            $t->boolean('is_placeholder')->default(false);
            $t->unsignedInteger('sort_order')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('quiz_steps', function (Blueprint $t) {
            $t->id();
            $t->string('key')->unique();           // e.g. region, season (answer field name)
            $t->string('question');
            $t->string('type')->default('tiles');  // tiles | select
            $t->unsignedInteger('sort_order')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('quiz_options', function (Blueprint $t) {
            $t->id();
            $t->foreignId('quiz_step_id')->constrained()->cascadeOnDelete();
            $t->string('label');
            $t->string('value');
            $t->string('icon')->nullable();        // key into components/icon
            $t->unsignedInteger('sort_order')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('enquiries', function (Blueprint $t) {
            $t->id();
            $t->string('form_name');               // hero-quiz, header-modal, package-quote, package-question, contact
            $t->string('first_name');
            $t->string('last_name');
            $t->string('email');
            $t->string('country_code', 8);
            $t->string('phone_national', 30);
            $t->string('phone_e164', 20);
            $t->string('travel_date')->nullable();
            $t->string('second_quote')->nullable();
            $t->text('special_request')->nullable();
            $t->foreignId('package_id')->nullable()->constrained()->nullOnDelete();
            $t->string('package_name')->nullable();
            $t->json('quiz_answers')->nullable();
            $t->string('status', 20)->default('new')->index();
            $t->text('notes')->nullable();
            // consent / chargeback evidence
            $t->boolean('consent_given');
            $t->text('consent_text');
            $t->string('terms_version', 20);
            $t->timestamp('consented_at');
            $t->string('ip_address', 45)->nullable();
            $t->text('user_agent')->nullable();
            $t->text('page_url')->nullable();
            // tracking
            $t->json('tracking_first')->nullable();
            $t->json('tracking_last')->nullable();
            // GHL sync
            $t->string('ghl_status', 20)->default('off'); // off|pending|synced|failed
            $t->string('ghl_contact_id')->nullable();
            $t->text('ghl_error')->nullable();
            $t->timestamp('ghl_synced_at')->nullable();
            $t->timestamps();
            $t->index('email');
        });
    }

    public function down(): void
    {
        foreach (['enquiries', 'quiz_options', 'quiz_steps', 'testimonials', 'faqs', 'package_images', 'media', 'packages', 'destinations', 'tour_types', 'pages', 'settings', 'admin_users', 'sessions'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
