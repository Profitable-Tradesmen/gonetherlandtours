<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\TourTypeController;
use Illuminate\Support\Facades\Route;

// Public site (all server-rendered)
Route::get('/', [PageController::class, 'home'])->name('home');
Route::permanentRedirect('/home-page-new', '/');   // the trial layout is now the home page
Route::get('/packages', [PackageController::class, 'index'])->name('packages.index');
Route::get('/packages/search', [PackageController::class, 'search'])->middleware('throttle:60,1')->name('packages.search');
Route::get('/packages/{package:slug}', [PackageController::class, 'show'])->name('packages.show');
Route::get('/destinations', [DestinationController::class, 'index'])->name('destinations.index');
Route::get('/destinations/{destination:slug}', [DestinationController::class, 'show'])->name('destinations.show');
Route::get('/travel-styles', [\App\Http\Controllers\TravelStyleController::class, 'index'])->name('travel-styles.index');
Route::get('/travel-styles/{travelStyle:slug}', [\App\Http\Controllers\TravelStyleController::class, 'show'])->name('travel-styles.show');
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post:slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/ways-to-travel', [TourTypeController::class, 'compare'])->name('ways-to-travel');
Route::get('/photo-credits', [PageController::class, 'photoCredits'])->name('photo-credits');
Route::get('/tours/{tourType:slug}', [TourTypeController::class, 'show'])->name('tour-types.show');
Route::get('/faq', [PageController::class, 'faq'])->name('faq');
Route::get('/testimonials', [PageController::class, 'testimonials'])->name('testimonials');
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

// Custom pages created in Admin > Pages (must stay LAST so it never shadows a real route)
Route::get('/{slug}', [PageController::class, 'custom'])->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')->name('pages.custom');
