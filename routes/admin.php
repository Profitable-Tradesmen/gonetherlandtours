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
        foreach (['tour-types' => Admin\TourTypeController::class, 'destinations' => Admin\DestinationController::class, 'faqs' => Admin\FaqController::class, 'testimonials' => Admin\TestimonialController::class, 'users' => Admin\AdminUserController::class] as $uri => $controller) {
            Route::get($uri, [$controller, 'index'])->name("$uri.index");
            Route::get("$uri/create", [$controller, 'create'])->name("$uri.create");
            Route::post($uri, [$controller, 'store'])->name("$uri.store");
            Route::get("$uri/{id}/edit", [$controller, 'edit'])->name("$uri.edit");
            Route::put("$uri/{id}", [$controller, 'update'])->name("$uri.update");
            Route::delete("$uri/{id}", [$controller, 'destroy'])->name("$uri.destroy");
            Route::post("$uri/{id}/move/{dir}", [$controller, 'move'])->whereIn('dir', ['up', 'down'])->name("$uri.move");
        }

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
        Route::get('pages/{page}/edit', [Admin\PageController::class, 'edit'])->name('pages.edit');
        Route::put('pages/{page}', [Admin\PageController::class, 'update'])->name('pages.update');
        Route::get('settings', [Admin\SettingsController::class, 'edit'])->name('settings');
        Route::put('settings', [Admin\SettingsController::class, 'update'])->name('settings.update');
        Route::get('media', [Admin\MediaController::class, 'index'])->name('media.index');
        Route::post('media', [Admin\MediaController::class, 'store'])->name('media.store');
        Route::put('media/{media}', [Admin\MediaController::class, 'update'])->name('media.update');
        Route::delete('media/{media}', [Admin\MediaController::class, 'destroy'])->name('media.destroy');
    });
});
