<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Something went wrong</title>
    {{-- Self-contained on purpose: this page must work even if the database or the build assets are unavailable. --}}
    <style>
        :root { color-scheme: light dark; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; font: 16px/1.6 system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; background: #F6F1EA; color: #0C0D11; padding: 24px; }
        main { max-width: 32rem; text-align: center; }
        p.code { letter-spacing: .12em; text-transform: uppercase; font-size: .8rem; font-weight: 700; color: #A54225; margin: 0; }
        h1 { font-size: 2rem; margin: .5rem 0; }
        a { display: inline-block; margin-top: 1rem; padding: .8rem 1.6rem; border-radius: 999px; background: #F2B743; color: #0C0D11; font-weight: 700; text-decoration: none; }
        @media (prefers-color-scheme: dark) { body { background: #0C0D11; color: #F6F1EA; } }
    </style>
</head>
<body>
<main>
    <p class="code">Error {{ $exception instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface ? $exception->getStatusCode() : 500 }}</p>
    <h1>Something went wrong on our side</h1>
    <p>We have been notified and are looking into it. Please try again in a few minutes. Your inquiry is never lost: if you were sending a form, try once more or contact us directly.</p>
    <a href="/">Back to home</a>
</main>
</body>
</html>
