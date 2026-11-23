@php
    $poster = $d['poster'] ?: 'images/hero-home.webp';
    $overlay = ! empty($overlayHeader);
@endphp
@push('head')
    {{-- The photo is the LCP element: preload the right size for the screen. --}}
    <link rel="preload" as="image" href="{{ asset_v($poster) }}" @if ($sets = static_srcset($poster)) imagesrcset="{{ $sets }}" imagesizes="100vw" @endif fetchpriority="high">
@endpush
<section class="relative isolate flex min-h-[88vh] items-center overflow-hidden bg-brand-900 text-white {{ $overlay ? 'pt-16 lg:pt-20' : '' }}">
    {{-- Photo first (works without JavaScript and on phones); the background video, if one exists, plays over it on larger screens. --}}
    <x-picture :src="$poster" alt="" :width="1600" :height="900" priority sizes="100vw" class="absolute inset-0 -z-30 h-full w-full object-cover" />
    @if ($d['show_video'] ?? true)<x-video name="homepage_banner" alt="" class="absolute inset-0 -z-20 h-full w-full object-cover" />@endif
    {{-- Golden-hour grade: warm amber/rose glow from the horizon, dusky violet-blue up top. Blends into the photo/video rather than covering it. --}}
    <div class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-t from-accent-600/75 via-rust/35 to-brand-900/45 mix-blend-soft-light"></div>
    <div class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-t from-accent/30 via-transparent to-transparent"></div>
    {{-- Contrast scrim behind the text: dark ink, strongest on the left and at the top (header). --}}
    <div class="pointer-events-none absolute inset-0 -z-10 bg-ink/35 lg:bg-transparent"></div>
    <div class="pointer-events-none absolute inset-0 -z-10 hidden bg-gradient-to-r from-ink/70 via-ink/35 to-transparent lg:block"></div>
    <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-32 bg-gradient-to-b from-ink/45 to-transparent"></div>
    <div class="container-site grid items-center gap-8 py-10 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)] lg:gap-x-12 lg:gap-y-8 lg:py-20">
        <div class="[text-shadow:0_2px_3px_rgb(0_0_0/0.35),0_6px_28px_rgb(0_0_0/0.55)]">
            @if ($d['eyebrow'])<p class="hero-in label-caps text-center text-accent lg:text-left">{{ $d['eyebrow'] }}</p>@endif
            <h1 style="--d:120ms" class="hero-in mt-4 text-center text-4xl font-semibold sm:text-5xl lg:text-left lg:text-5xl xl:text-6xl">{!! emphasize($d['heading'], 'text-accent') !!}</h1>
            @if ($d['subheading'])<p style="--d:240ms" class="hero-in mt-4 hidden max-w-xl text-center text-sm font-medium text-white sm:mx-auto sm:mt-5 sm:block sm:text-lg lg:mx-0 lg:text-left">{{ $d['subheading'] }}</p>@endif
            <div style="--d:360ms" class="hero-in mt-7 hidden flex-wrap gap-3 sm:flex sm:justify-center lg:justify-start">
                @if ($d['secondary_label'])<x-cta-button variant="outline" :label="$d['secondary_label']" :url="$d['secondary_url']" class="text-white" />@endif
            </div>
        </div>
        <div style="--d:300ms" class="hero-in"><x-quiz :steps="\App\Support\SiteData::quizSteps()" /></div>
    </div>
</section>
