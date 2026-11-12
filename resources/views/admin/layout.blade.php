<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Admin') | {{ setting('brand_name', 'GoNetherlandTours') }} Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Trix rich text editor: only loaded in the admin --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/trix/2.1.1/trix.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/trix/2.1.1/trix.min.js" defer></script>
    <style>trix-toolbar .trix-button-group--file-tools{display:none}trix-editor{min-height:12rem;background:#fff}</style>
</head>
<body x-data="{ nav: false }" class="bg-sand/60 text-ink">
@php
    $nav = [
        ['admin.dashboard', 'Dashboard'], ['admin.enquiries.index', 'Enquiries'], ['admin.packages.index', 'Packages'],
        ['admin.tour-types.index', 'Tour types'], ['admin.destinations.index', 'Destinations'], ['admin.quiz.index', 'Quiz'],
        ['admin.pages.index', 'Pages'], ['admin.faqs.index', 'FAQs'], ['admin.testimonials.index', 'Testimonials'],
        ['admin.media.index', 'Media'], ['admin.settings', 'Settings'], ['admin.users.index', 'Admin users'],
    ];
@endphp
<div class="flex min-h-screen">
    <aside class="fixed inset-y-0 left-0 z-40 w-60 -translate-x-full overflow-y-auto bg-ink p-4 text-white transition lg:static lg:translate-x-0" :class="nav && '!translate-x-0'">
        <a href="{{ route('admin.dashboard') }}" class="mb-6 block font-display text-xl">{{ setting('brand_name', 'GoNetherlandTours') }}<span class="block text-xs font-sans text-white/60">Admin</span></a>
        <nav class="space-y-1 text-sm" aria-label="Admin">
            @foreach ($nav as [$r, $label])
                <a href="{{ route($r) }}" @class(['block rounded-lg px-3 py-2', 'bg-accent text-ink font-semibold' => request()->routeIs(Str::beforeLast($r, '.').'*') || request()->routeIs($r), 'hover:bg-white/10' => ! request()->routeIs(Str::beforeLast($r, '.').'*')])>{{ $label }}</a>
            @endforeach
            <a href="{{ url('/') }}" target="_blank" class="block rounded-lg px-3 py-2 text-white/70 hover:bg-white/10">View site &nearr;</a>
            <form method="POST" action="{{ route('admin.logout') }}">@csrf<button class="mt-4 w-full rounded-lg px-3 py-2 text-left text-white/70 hover:bg-white/10">Log out</button></form>
        </nav>
    </aside>
    <div class="min-w-0 flex-1">
        <div class="flex items-center gap-3 border-b border-sand-200 bg-white px-4 py-3 lg:hidden">
            <button type="button" @click="nav = !nav" class="rounded p-1" aria-label="Menu"><x-icon name="menu" /></button><span class="font-semibold">Admin</span>
        </div>
        <main class="mx-auto max-w-6xl p-4 sm:p-6 lg:p-8">
            @if (session('status'))<div class="mb-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-900" role="status">{{ session('status') }}</div>@endif
            @if ($errors->any())
                <div class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-900" role="alert"><p class="font-semibold">Please fix the following:</p><ul class="list-disc pl-5">@foreach ($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul></div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
