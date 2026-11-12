<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Rate limit enquiry submissions per IP.
        RateLimiter::for('enquiry', fn (Request $r) => Limit::perMinute(config('gnt.enquiry_rate_limit'))->by($r->ip()));
        // Throttle admin login attempts per IP + email.
        RateLimiter::for('admin-login', fn (Request $r) => Limit::perMinute(5)->by($r->ip().'|'.mb_strtolower((string) $r->input('email'))));
    }
}
