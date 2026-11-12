@php
    $seo = $seo ?? \App\Support\Seo::make();
    $gtm = setting('gtm_id');
    $brand = setting('brand_name', 'GoNetherlandTours');
    $phone = setting('phone');
    $tourTypes = \App\Models\TourType::active()->get();
    $destinations = \App\Models\Destination::active()->get();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $seo['title'] }}</title>
    <meta name="description" content="{{ $seo['description'] }}">
    <link rel="canonical" href="{{ $seo['canonical'] }}">
    @if ($seo['noindex'])<meta name="robots" content="noindex, nofollow">@else<meta name="robots" content="index, follow, max-image-preview:large">@endif
    <meta name="theme-color" content="#2F6285">
    @if (setting('favicon'))<link rel="icon" href="{{ asset(setting('favicon')) }}">@else<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='7' fill='%23437DA5'/%3E%3Ctext x='16' y='23' font-size='20' text-anchor='middle' fill='%23F2B743' font-family='Georgia' font-style='italic'%3Eg%3C/text%3E%3C/svg%3E">@endif

    <meta property="og:site_name" content="{{ $brand }}">
    <meta property="og:type" content="{{ $seo['type'] }}">
    <meta property="og:title" content="{{ $seo['title'] }}">
    <meta property="og:description" content="{{ $seo['description'] }}">
    <meta property="og:url" content="{{ $seo['canonical'] }}">
    <meta property="og:image" content="{{ $seo['image'] }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seo['title'] }}">
    <meta name="twitter:description" content="{{ $seo['description'] }}">
    <meta name="twitter:image" content="{{ $seo['image'] }}">

    <script>document.documentElement.classList.add('js')</script>
    @stack('head')
    @foreach ($seo['jsonld'] as $block)
        <script type="application/ld+json">{!! json_encode($block, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
    @endforeach

    @if ($gtm)
        <script>window.dataLayer=window.dataLayer||[];</script>
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','{{ e($gtm) }}');</script>
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data class="min-h-screen bg-white pb-16 lg:pb-0">
    @if ($gtm)<noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ e($gtm) }}" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>@endif
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-2 focus:top-2 focus:z-[100] focus:rounded focus:bg-accent focus:px-4 focus:py-2">Skip to main content</a>

    @include('partials.topbar')
    @include('partials.header')

    <main id="main">@yield('content')</main>

    @include('partials.footer')

    {{-- Header modal enquiry form (reachable from every page) --}}
    <x-modal title="Start your adventure">
        <p class="mt-1 text-sm text-ink/75">Fill out this form, save up to {{ setting('modal_discount', '[CONFIRM: discount %]') }} on your trip.</p>
        <div class="mt-4"><x-enquiry-form form="header-modal" idp="modal" variant="modal" submit="Send My Enquiry" /></div>
    </x-modal>

    {{-- Mobile sticky action bar --}}
    <div class="fixed inset-x-0 bottom-0 z-40 flex gap-2 border-t border-sand-200 bg-white p-2 shadow-[0_-6px_20px_-10px_rgb(0_0_0/.4)] lg:hidden">
        @if ($phone)<a href="tel:{{ preg_replace('/[^+\d]/', '', $phone) }}" class="btn btn-secondary flex-1"><x-icon name="phone" class="h-4 w-4" /> Call</a>@endif
        <button type="button" class="btn btn-primary flex-[2]" @click="$store.modal.show()">Get a Free Quote</button>
    </div>
    @stack('scripts')
</body>
</html>
