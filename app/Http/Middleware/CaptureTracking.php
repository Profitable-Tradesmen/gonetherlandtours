<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * First-touch attribution. On the first visit that carries UTM/click-ID parameters (or the
 * first visit at all), store them in a 30-day cookie. Later visits never overwrite it.
 * The latest touch is kept in the session and is read when an enquiry is submitted.
 */
class CaptureTracking
{
    public function handle(Request $request, Closure $next): Response
    {
        $name = config('gnt.tracking_cookie');
        $keys = config('gnt.tracking_keys');
        $current = array_filter($request->only($keys), fn ($v) => is_string($v) && $v !== '');
        $current = array_map(fn ($v) => mb_substr($v, 0, 255), $current);

        $isTrackable = $request->isMethod('GET') && ! $request->is('admin*') && ! $request->ajax();

        if ($isTrackable && $current) {
            // Latest touch = most recent visit that carried tracking parameters.
            $request->session()->put('tracking_last', $current + [
                'referrer' => $this->referrer($request),
                'landing_page' => mb_substr($request->fullUrl(), 0, 500),
            ]);
        }

        $response = $next($request);

        if ($isTrackable && ! $request->cookie($name)) {
            $first = $current + [
                'referrer' => $this->referrer($request),
                'landing_page' => mb_substr($request->fullUrl(), 0, 500),
                'first_seen' => now()->toIso8601String(),
            ];
            // Plain (unencrypted) JSON cookie value is avoided: Laravel encrypts cookies automatically.
            $response->headers->setCookie(cookie($name, json_encode($first), 60 * 24 * config('gnt.tracking_days')));
            $request->attributes->set('tracking_first_set', $first);
        }

        return $response;
    }

    private function referrer(Request $request): ?string
    {
        $ref = $request->headers->get('referer');

        return ($ref && parse_url($ref, PHP_URL_HOST) !== $request->getHost()) ? mb_substr($ref, 0, 500) : null;
    }
}
