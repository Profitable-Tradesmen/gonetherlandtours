<?php

use App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [Admin\AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [Admin\AuthController::class, 'login'])->middleware('throttle:admin-login')->name('login.attempt');

    Route::middleware('admin')->group(function () {
        Route::post('logout', [Admin\AuthController::class, 'logout'])->name('logout');
        Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

        // Enquiries
        Route::get('enquiries/export', [Admin\EnquiryController::class, 'export'])->name('enquiries.export');
        Route::get('enquiries', [Admin\EnquiryController::class, 'index'])->name('enquiries.index');
        Route::get('enquiries/{enquiry}', [Admin\EnquiryController::class, 'show'])->name('enquiries.show');
        Route::put('enquiries/{enquiry}', [Admin\EnquiryController::class, 'update'])->name('enquiries.update');
        Route::delete('enquiries/{enquiry}', [Admin\EnquiryController::class, 'destroy'])->name('enquiries.destroy');
        Route::post('enquiries/{enquiry}/ghl-retry', [Admin\EnquiryController::class, 'retryGhl'])->name('enquiries.ghl-retry');
        Route::post('enquiries/{enquiry}/sheets-retry', [Admin\EnquiryController::class, 'retrySheets'])->name('enquiries.sheets-retry');

        // Packages
        Route::get('packages', [Admin\PackageController::class, 'index'])->name('packages.index');
        Route::get('packages/create', [Admin\PackageController::class, 'create'])->name('packages.create');
        Route::post('packages', [Admin\PackageController::class, 'store'])->name('packages.store');
        Route::get('packages/{package}/edit', [Admin\PackageController::class, 'edit'])->name('packages.edit');
        Route::put('packages/{package}', [Admin\PackageController::class, 'update'])->name('packages.update');
        Route::delete('packages/{package}', [Admin\PackageController::class, 'destroy'])->name('packages.destroy');
        Route::post('packages/{package}/duplicate', [Admin\PackageController::class, 'duplicate'])->name('packages.duplicate');
        Route::post('packages/{package}/toggle/{field}', [Admin\PackageController::class, 'toggle'])->name('packages.toggle');
        Route::post('packages/{package}/move/{dir}', [Admin\PackageController::class, 'move'])->whereIn('dir', ['up', 'down'])->name('packages.move');

        // Simple list resources (generic CRUD)
        foreach (['navigation' => Admin\NavigationController::class, 'tour-types' => Admin\TourTypeController::class, 'destinations' => Admin\DestinationController::class, 'travel-styles' => Admin\TravelStyleController::class, 'faqs' => Admin\FaqController::class, 'testimonials' => Admin\TestimonialController::class, 'blog' => Admin\BlogController::class, 'users' => Admin\AdminUserController::class] as $uri => $controller) {
            Route::get($uri, [$controller, 'index'])->name("$uri.index");
            Route::get("$uri/create", [$controller, 'create'])->name("$uri.create");
            Route::post($uri, [$controller, 'store'])->name("$uri.store");
            Route::get("$uri/{id}/edit", [$controller, 'edit'])->name("$uri.edit");
            Route::put("$uri/{id}", [$controller, 'update'])->name("$uri.update");
            Route::delete("$uri/{id}", [$controller, 'destroy'])->name("$uri.destroy");
            Route::post("$uri/{id}/move/{dir}", [$controller, 'move'])->whereIn('dir', ['up', 'down'])->name("$uri.move");
        }

        // Header & Footer: custom code snippets (administrators only)
        Route::get('snippets', [Admin\SnippetController::class, 'index'])->name('snippets.index');
        Route::get('snippets/create', [Admin\SnippetController::class, 'create'])->name('snippets.create');
        Route::post('snippets', [Admin\SnippetController::class, 'store'])->name('snippets.store');
        Route::get('snippets/{id}/edit', [Admin\SnippetController::class, 'edit'])->name('snippets.edit');
        Route::put('snippets/{id}', [Admin\SnippetController::class, 'update'])->name('snippets.update');
        Route::delete('snippets/{id}', [Admin\SnippetController::class, 'destroy'])->name('snippets.destroy');
        Route::post('snippets/{id}/toggle', [Admin\SnippetController::class, 'toggle'])->name('snippets.toggle');

        // Quiz editor
        Route::get('quiz', [Admin\QuizController::class, 'index'])->name('quiz.index');
        Route::get('quiz/create', [Admin\QuizController::class, 'create'])->name('quiz.create');
        Route::post('quiz', [Admin\QuizController::class, 'store'])->name('quiz.store');
        Route::get('quiz/{step}/edit', [Admin\QuizController::class, 'edit'])->name('quiz.edit');
        Route::put('quiz/{step}', [Admin\QuizController::class, 'update'])->name('quiz.update');
        Route::delete('quiz/{step}', [Admin\QuizController::class, 'destroy'])->name('quiz.destroy');
        Route::post('quiz/{step}/toggle', [Admin\QuizController::class, 'toggle'])->name('quiz.toggle');
        Route::post('quiz/{step}/move/{dir}', [Admin\QuizController::class, 'move'])->whereIn('dir', ['up', 'down'])->name('quiz.move');

        // Pages, settings, media
        Route::get('pages', [Admin\PageController::class, 'index'])->name('pages.index');
        Route::get('pages/create', [Admin\PageController::class, 'create'])->name('pages.create');
        Route::post('pages', [Admin\PageController::class, 'store'])->name('pages.store');
        Route::get('pages/{page}/edit', [Admin\PageController::class, 'edit'])->name('pages.edit');
        Route::put('pages/{page}', [Admin\PageController::class, 'update'])->name('pages.update');
        Route::post('pages/{page}/publish', [Admin\PageController::class, 'togglePublish'])->name('pages.publish');
        Route::delete('pages/{page}', [Admin\PageController::class, 'destroy'])->name('pages.destroy');
        Route::post('pages/{page}/sections', [Admin\PageController::class, 'addSection'])->name('pages.sections.add');
        Route::get('pages/{page}/sections/{section}/edit', [Admin\PageController::class, 'editSection'])->name('pages.sections.edit');
        Route::put('pages/{page}/sections/{section}', [Admin\PageController::class, 'updateSection'])->name('pages.sections.update');
        Route::post('pages/{page}/sections/{section}/toggle', [Admin\PageController::class, 'toggleSection'])->name('pages.sections.toggle');
        Route::post('pages/{page}/sections/{section}/move/{dir}', [Admin\PageController::class, 'moveSection'])->whereIn('dir', ['up', 'down'])->name('pages.sections.move');
        Route::delete('pages/{page}/sections/{section}', [Admin\PageController::class, 'destroySection'])->name('pages.sections.destroy');
        Route::get('settings', [Admin\SettingsController::class, 'edit'])->name('settings');
        Route::put('settings', [Admin\SettingsController::class, 'update'])->name('settings.update');
        Route::get('ghl', [Admin\GhlController::class, 'edit'])->name('ghl');
        Route::put('ghl', [Admin\GhlController::class, 'update'])->name('ghl.update');
        Route::post('ghl/test', [Admin\GhlController::class, 'test'])->middleware('throttle:10,1')->name('ghl.test');
        Route::post('ghl/setup', [Admin\GhlController::class, 'setup'])->middleware('throttle:10,1')->name('ghl.setup');
        Route::get('sheets', [Admin\SheetsController::class, 'edit'])->name('sheets');
        Route::put('sheets', [Admin\SheetsController::class, 'update'])->name('sheets.update');
        Route::post('sheets/test', [Admin\SheetsController::class, 'test'])->middleware('throttle:10,1')->name('sheets.test');
        Route::post('sheets/backfill', [Admin\SheetsController::class, 'backfill'])->middleware('throttle:6,1')->name('sheets.backfill');
        Route::get('media',[Admin\MediaController::class, 'index'])->name('media.index');
        Route::post('media', [Admin\MediaController::class, 'store'])->name('media.store');
        Route::put('media/{media}', [Admin\MediaController::class, 'update'])->name('media.update');
        Route::delete('media/{media}', [Admin\MediaController::class, 'destroy'])->name('media.destroy');
    });
});
