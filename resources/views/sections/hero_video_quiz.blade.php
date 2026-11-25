@php
    $poster = $d['poster'] ?: 'images/hero-home.webp';
    $overlay = ! empty($overlayHeader);
    $chips = $d['chips'] ?? [];
@endphp
@push('head')
    {{-- The photo is the LCP element: preload the right size for the screen. --}}
    <link rel="preload" as="image" href="{{ asset_v($poster) }}" @if ($sets = static_srcset($poster)) imagesrcset="{{ $sets }}" imagesizes="100vw" @endif fetchpriority="high">
@endpush
<section class="hero-pad relative isolate flex min-h-[96vh] items-center overflow-hidden bg-brand-900 text-white {{ $overlay ? 'pt-24 lg:pt-32' : 'pt-10' }} {{ count($chips) ? "pb-28 sm:pb-36" : "" }}">
    {{-- Photo first (works without JavaScript and on phones); the background video, if one exists, plays over it on larger screens. --}}
    <x-picture :src="$poster" alt="" :width="1600" :height="900" priority sizes="100vw" class="absolute inset-0 -z-30 h-full w-full object-cover" />
    @if ($d['show_video'] ?? true)<x-video name="homepage_banner" alt="" class="absolute inset-0 -z-20 h-full w-full object-cover" />@endif
    {{-- Golden-hour grade: warm amber/rose glow from the horizon, dusky violet-blue up top. Blends into the photo/video rather than covering it. --}}
    <div class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-t from-accent-600/55 via-rust/20 to-brand-900/20 mix-blend-soft-light"></div>
    <div class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-t from-accent/30 via-transparent to-transparent"></div>
    {{-- Contrast scrim behind the text: dark ink, strongest on the left and at the top (header). --}}
    <div class="pointer-events-none absolute inset-0 -z-10 bg-ink/20 lg:bg-transparent"></div>
    <div class="pointer-events-none absolute inset-0 -z-10 hidden bg-gradient-to-r from-ink/45 via-ink/15 to-transparent lg:block"></div>
    <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-32 bg-gradient-to-b from-ink/30 to-transparent"></div>
    <div class="container-site grid items-center gap-10 py-10 md:py-0 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)] lg:gap-x-12 lg:gap-y-8">
        <div class="[text-shadow:0_2px_3px_rgb(0_0_0/0.35),0_6px_28px_rgb(0_0_0/0.55)]">
            @if ($d['eyebrow'])<p class="hero-in text-center lg:text-left"><span class="label-caps inline-block rounded-full bg-ink/85 px-4 py-1.5 text-[0.8rem] font-bold [text-shadow:none]" style="color:#F2B743">{{ $d['eyebrow'] }}</span></p>@endif
            <h1 style="--d:120ms" class="hero-in mt-4 text-center text-4xl font-semibold sm:text-5xl lg:text-left lg:text-5xl xl:text-6xl">{!! emphasize($d['heading'], 'text-accent') !!}</h1>
            @if ($d['subheading'])<p style="--d:240ms" class="hero-in mt-4 hidden max-w-xl text-center text-sm font-medium text-white sm:mx-auto sm:mt-5 sm:block sm:text-lg lg:mx-0 lg:text-left">{{ $d['subheading'] }}</p>@endif
            {{-- The three ways to travel as a quiet row of links (icon + name), like a short menu under the intro text. --}}
            @php $ttypes = \App\Support\SiteData::tourTypes(); @endphp
            @if ($ttypes->isNotEmpty())
                <ul style="--d:360ms" class="hero-in mt-7 hidden flex-wrap items-center justify-center gap-x-6 gap-y-3 sm:flex lg:justify-start" aria-label="Ways to travel">
                    @foreach ($ttypes as $tt)
                        <li class="{{ $loop->first ? '' : 'sm:border-l sm:border-white/35 sm:pl-6' }}">
                            <a href="{{ route('tour-types.show', $tt) }}" class="label-caps inline-flex min-h-[2.75rem] items-center gap-2 text-xs font-bold text-white transition hover:text-accent"><x-icon :name="$tt->icon ?: 'check'" class="h-5 w-5 text-accent" />{{ $tt->name }}</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
        <div style="--d:300ms" class="hero-in"><x-quiz :steps="\App\Support\SiteData::quizSteps()" /></div>
    </div>
    @if (count($chips))
        {{-- Full-width navy band along the bottom of the hero: a pin and "Where to?", then the destinations as a slowly sliding row of caps separated by gold dots (the track holds two identical groups; pauses on hover or focus). --}}
        <div class="absolute inset-x-0 bottom-0 z-10 border-t border-white/15 bg-brand-900/85 backdrop-blur-sm">
            <div class="container-site flex items-center gap-4 py-3.5">
                <p class="label-caps flex shrink-0 items-center gap-2 text-xs font-bold text-accent"><x-icon name="map" class="h-4 w-4" /><span>{{ ($d["chips_label"] ?? "") !== "" ? $d["chips_label"] : "Where to?" }}</span></p>
                <span class="h-5 w-px shrink-0 bg-white/25" aria-hidden="true"></span>
                <div class="marquee min-w-0 flex-1" style="animation:none;--marquee-time:45s" role="region" aria-label="Destinations">
                    <div class="marquee-track">
                        @foreach (range(1, 2) as $copy)
                            <ul class="marquee-group" style="gap:1.5rem;padding-right:1.5rem" @if ($copy === 2) aria-hidden="true" @endif>
                                @foreach (array_merge($chips, $chips) as $c)
                                    <li class="flex items-center gap-6"><a @if ($copy === 2) tabindex="-1" @endif href="{{ str_starts_with((string) $c['url'], '/') ? url($c['url']) : ($c['url'] ?: url('/destinations')) }}" class="inline-flex min-h-[2.5rem] items-center whitespace-nowrap text-xs font-bold uppercase tracking-[0.16em] text-white transition hover:text-accent">{{ $c['label'] }}</a><i class="h-1.5 w-1.5 rounded-full bg-accent" aria-hidden="true"></i></li>
                                @endforeach
                            </ul>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif
</section>
