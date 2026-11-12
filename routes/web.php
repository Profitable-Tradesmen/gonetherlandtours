<?php

use App\Http\Controllers\DestinationController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

// Public site (all server-rendered)
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/packages', [PackageController::class, 'index'])->name('packages.index');
Route::get('/packages/{package:slug}', [PackageController::class, 'show'])->name('packages.show');
Route::get('/destinations/{destination:slug}', [DestinationController::class, 'show'])->name('destinations.show');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/terms-and-conditions', fn () => app(PageController::class)->legal('terms'))->name('terms');
Route::get('/privacy-policy', fn () => app(PageController::class)->legal('privacy'))->name('privacy');

// One endpoint for EVERY enquiry form (quiz, modal, package, contact)
Route::post('/enquiry', [EnquiryController::class, 'store'])->middleware('throttle:enquiry')->name('enquiry.store');
Route::get('/thank-you', [EnquiryController::class, 'thanks'])->name('thank-you');

Route::get('/sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

require __DIR__.'/admin.php';
