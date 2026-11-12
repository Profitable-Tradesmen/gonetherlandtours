<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Admin login | {{ setting('brand_name', 'GoNetherlandTours') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen items-center justify-center bg-sand p-4">
<main class="w-full max-w-sm rounded-2xl bg-white p-7 shadow-card">
    <h1 class="font-display text-3xl">Admin login</h1>
    <form method="POST" action="{{ route('admin.login.attempt') }}" class="mt-6 space-y-4">
        @csrf
        <div><label for="email" class="field-label">Email</label><input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="field"></div>
        <div><label for="password" class="field-label">Password</label><input id="password" name="password" type="password" required autocomplete="current-password" class="field"></div>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember" value="1"> Keep me signed in</label>
        @error('email')<p class="field-error" role="alert">{{ $message }}</p>@enderror
        @if ($errors->has('throttle') || session('throttle'))<p class="field-error">Too many attempts. Try again shortly.</p>@endif
        <button class="btn btn-secondary btn-block" type="submit">Log in</button>
    </form>
</main>
</body>
</html>
