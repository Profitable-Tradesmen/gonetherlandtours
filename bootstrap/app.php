<?php

use App\Http\Middleware\AdminAuthenticate;
use App\Http\Middleware\CaptureTracking;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['admin' => AdminAuthenticate::class]);
        $middleware->web(append: [SecurityHeaders::class, CaptureTracking::class]);
        // Trust proxy headers (Hostinger/Cloudflare) so https and client IPs are correct.
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
