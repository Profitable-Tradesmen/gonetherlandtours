@php
    $seo = $seo ?? \App\Support\Seo::make();
    $gtm = setting('gtm_id');
    $brand = setting('brand_name', 'GoNetherlandTours');
    $phone = setting('phone');
    $tourTypes = \App\Support\SiteData::tourTypes();
    $destinations = \App\Support\SiteData::destinations();
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
    <link rel="icon" href="{{ asset("favicon.ico") }}" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset("images/favicon-32.png") }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset("images/favicon-192.png") }}">
    <link rel="apple-touch-icon" href="{{ asset("images/apple-touch-icon.png") }}">

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
    {{-- Preload the two fonts every page uses (body + headings) so text is laid out once with the final fonts (no re-wrap / layout shift). --}}
    @foreach (['manrope'] as $font)
        @php $fontUrl = rescue(fn () => Vite::asset('node_modules/@fontsource-variable/'.$font.'/files/'.$font.'-latin-wght-normal.woff2'), null, false); @endphp
        @if ($fontUrl)<link rel="preload" as="font" type="font/woff2" href="{{ $fontUrl }}" crossorigin>@endif
    @endforeach
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Custom code: Admin > Header & Footer (head) --}}
    {{ snippets('head') }}
</head>
<body x-data class="min-h-screen bg-white pb-16 lg:pb-0">
    {{ snippets('body_start') }}
    @if ($gtm)<noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ e($gtm) }}" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>@endif
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-2 focus:top-2 focus:z-[100] focus:rounded focus:bg-accent focus:px-4 focus:py-2">Skip to main content</a>

    @include('partials.topbar')

    @include('partials.header', ['overlayHeader' => $overlayHeader ?? false])

    <main id="main">@yield('content')</main>

    @include('partials.footer')

    {{-- Header modal inquiry form (reachable from every page) --}}
    <x-modal :title="text('modal_title')">
        <p x-show="$store.modal.pkg" x-cloak class="mt-2 rounded-lg bg-sand px-3 py-2 text-sm font-semibold text-brand-700" x-text="'Package: ' + ($store.modal.pkg?.name ?? '')"></p>
        <p class="mt-1 text-sm text-ink/75">{{ str_replace('{discount}', setting('modal_discount', '10%'), text('modal_intro')) }}</p>
        <div class="mt-4"><x-enquiry-form form="header-modal" idp="modal" variant="modal" submit="Send My Inquiry" /></div>
    </x-modal>

    {{-- Mobile sticky action bar --}}
    <div class="fixed inset-x-0 bottom-0 z-40 flex gap-2 border-t border-sand-200 bg-white p-2 shadow-[0_-6px_20px_-10px_rgb(0_0_0/.4)] lg:hidden">
        @if ($phone)<a href="tel:{{ preg_replace('/[^+\d]/', '', $phone) }}" class="btn btn-secondary flex-1"><x-icon name="phone" class="h-4 w-4" /> {{ text('mobile_bar_call_label') }}</a>@endif
        <button type="button" class="btn btn-primary flex-[2]" @click="$store.modal.show()">{{ text('mobile_bar_quote_label') }}</button>
    </div>
    {{-- Desktop sticky contact bar: slides up from the bottom once the visitor has scrolled past the first screen. Phones use the action bar above. --}}
    <div x-data="{ on: false }" x-init="on = window.scrollY > 600" @scroll.window.passive="on = window.scrollY > 600" x-show="on" x-cloak
         x-transition:enter="transition duration-300" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
         class="fixed inset-x-0 bottom-0 z-40 hidden border-t border-sand-200 bg-white/95 shadow-[0_-8px_24px_-12px_rgb(0_0_0/.35)] backdrop-blur lg:block" role="region" aria-label="Contact">
        <div class="container-site flex items-center justify-between gap-6 py-3">
            @php $barPhone = $phone ?: '+1 000-000-0000'; @endphp
            @if ($barPhone)
                <p class="text-sm text-ink/80">Consult with our Travel Specialists at
                    <a href="tel:{{ preg_replace('/[^+\d]/', '', $barPhone) }}" class="ml-2 font-display text-xl font-medium text-brand-900 hover:text-rust">{{ $barPhone }}</a>
                </p>
            @else
                <p class="text-sm text-ink/80">Free quote from our Netherlands specialists, with no obligation.</p>
            @endif
            <button type="button" class="btn btn-primary !px-6 !py-2.5" @click="$store.modal.show()">Request a quote</button>
        </div>
    </div>
    @stack('scripts')
    {{ snippets('body_end') }}
</body>
</html>
