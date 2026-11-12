<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/** Server-side reCAPTCHA v2 verification. Fails CLOSED: any error or timeout means "not verified". */
class RecaptchaService
{
    public function verify(?string $token, ?string $ip = null): bool
    {
        $secret = recaptcha_secret_key();
        if (! $secret || ! $token) {
            return false;
        }

        try {
            $res = Http::asForm()->timeout(8)->post('https://www.google.com/recaptcha/api/siteverify', array_filter([
                'secret' => $secret,
                'response' => $token,
                'remoteip' => $ip,
            ]));

            return $res->successful() && $res->json('success') === true;
        } catch (\Throwable $e) {
            Log::warning('reCAPTCHA verification failed to run: '.$e->getMessage());

            return false;
        }
    }
}
