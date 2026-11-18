@php
    $poster = $d['poster'] ?: 'images/hero-home.webp';
    $overlay = ! empty($overlayHeader);
@endphp
@push('head')
    {{-- The photo is the LCP element: preload the right size for the screen. --}}
    <link rel="preload" as="image" href="{{ asset($poster) }}" @if ($sets = static_srcset($poster)) imagesrcset="{{ $sets }}" imagesizes="100vw" @endif fetchpriority="high">
@endpush
<section class="relative isolate flex min-h-[88vh] items-center overflow-hidden bg-brand-900 text-white {{ $overlay ? 'pt-16 lg:pt-20' : '' }}">
    {{-- Photo first (works without JavaScript and on phones); the background video, if one exists, plays over it on larger screens. --}}
    <x-picture :src="$poster" alt="" :width="1600" :height="900" priority sizes="100vw" class="absolute inset-0 -z-30 h-full w-full object-cover" />
    <x-video name="homepage_banner" alt="" class="absolute inset-0 -z-20 h-full w-full object-cover" />
    <div class="absolute inset-0 -z-10 bg-gradient-to-t from-ink/80 via-ink/40 to-ink/30"></div>
    <div class="absolute inset-x-0 top-0 -z-10 h-40 bg-gradient-to-b from-ink/60 to-transparent"></div>
    <div class="absolute inset-0 -z-10 hidden bg-gradient-to-r from-ink/60 via-ink/20 to-transparent lg:block"></div>
    @if (($d['layout'] ?? 'centered') === 'centered')
    {{-- Centered layout: label, headline, intro, the quiz in a framed card, then a one-line trust row. --}}
    <div class="container-site flex flex-col items-center py-10 text-center lg:py-16">
        @if ($d['eyebrow'])<p class="hero-in label-caps inline-flex items-center gap-3 text-accent"><span class="h-px w-8 bg-accent" aria-hidden="true"></span><span class="h-2 w-2 rounded-full bg-rust ring-2 ring-white/60" aria-hidden="true"></span>{{ $d['eyebrow'] }}</p>@endif
        <h1 style="--d:120ms" class="hero-in mt-4 max-w-4xl text-4xl font-medium sm:text-5xl lg:text-6xl">{!! emphasize($d['heading'], 'text-accent') !!}</h1>
        @if ($d['subheading'])<p style="--d:240ms" class="hero-in mt-4 line-clamp-3 max-w-2xl text-sm text-white/90 sm:mt-5 sm:line-clamp-none sm:text-lg">{{ $d['subheading'] }}</p>@endif
        @if ($d['survey_line'])<p style="--d:300ms" class="hero-in mt-6 font-display text-lg italic text-white sm:text-xl">{{ $d['survey_line'] }}</p>@endif
        <div style="--d:360ms" class="hero-in mt-5 w-full max-w-2xl text-left"><x-quiz :steps="\App\Support\SiteData::quizSteps()" /></div>
        <ul style="--d:480ms" class="hero-in mt-7 hidden flex-wrap justify-center gap-x-5 gap-y-2 text-sm font-semibold sm:flex" aria-label="Why book with us">
            @foreach ($d['badges'] as $i => $badge)
                <li class="flex items-center gap-2 {{ $i > 0 ? 'sm:border-l sm:border-white/30 sm:pl-5' : '' }}"><x-icon :name="['shield', 'map', 'star'][$i % 3]" class="h-5 w-5 shrink-0 text-accent" /> {{ $badge['text'] }}</li>
            @endforeach
        </ul>
    </div>
    @else
    <div class="container-site grid items-center gap-8 py-10 lg:grid-cols-[1.1fr_0.9fr] lg:gap-10 lg:py-20">
        <div>
            @if ($d['eyebrow'])<p class="hero-in label-caps text-center text-accent lg:text-left">{{ $d['eyebrow'] }}</p>@endif
            <h1 style="--d:120ms" class="hero-in mt-4 text-center text-4xl font-medium sm:text-5xl lg:text-left lg:text-6xl">{!! emphasize($d['heading'], 'text-accent') !!}</h1>
            @if ($d['subheading'])<p style="--d:240ms" class="hero-in mt-4 line-clamp-3 max-w-xl text-center text-sm text-white/90 sm:mx-auto sm:mt-5 sm:line-clamp-none sm:text-lg lg:mx-0 lg:text-left">{{ $d['subheading'] }}</p>@endif
            <div style="--d:360ms" class="hero-in mt-7 hidden flex-wrap gap-3 sm:flex sm:justify-center lg:justify-start">
                @if ($d['primary_label'])<x-button @click="$store.modal.show()">{{ $d['primary_label'] }}</x-button>@endif
                @if ($d['secondary_label'])<x-cta-button variant="outline" :label="$d['secondary_label']" :url="$d['secondary_url']" class="text-white" />@endif
            </div>
            <ul style="--d:480ms" class="hero-in mt-8 hidden flex-wrap gap-x-6 gap-y-2 text-sm font-semibold sm:flex sm:justify-center lg:justify-start" aria-label="Why book with us">
                @foreach ($d['badges'] as $i => $badge)
                    <li class="flex items-center gap-2"><x-icon :name="['shield', 'map', 'star'][$i % 3]" class="h-5 w-5 shrink-0 text-accent" /> {{ $badge['text'] }}</li>
                @endforeach
            </ul>
        </div>
        <div style="--d:300ms" class="hero-in"><x-quiz :steps="\App\Support\SiteData::quizSteps()" /></div>
    </div>
    @endif
</section>
