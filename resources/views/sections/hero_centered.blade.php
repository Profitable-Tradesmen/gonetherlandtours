@push('head')
    @if ($d['image'])<link rel="preload" as="image" href="{{ asset_v($d['image']) }}" @if ($sets = static_srcset($d['image'])) imagesrcset="{{ $sets }}" imagesizes="100vw" @endif fetchpriority="high">@endif
@endpush
{{-- Centered photo hero (reference: goeurotours.com): caps label and heading, one line of intro, two buttons and a row of destination chips. --}}
<section class="relative isolate flex min-h-[88vh] items-center overflow-hidden bg-brand-900 text-center text-white {{ ! empty($overlayHeader) ? 'pb-16 pt-28 sm:pb-24 sm:pt-36' : 'py-16 sm:py-24' }}">
    @if ($d['image'])<x-picture :src="$d['image']" :alt="$d['image_alt']" :width="1920" :height="1080" priority sizes="100vw" class="absolute inset-0 -z-20 h-full w-full object-cover" />@endif
    <div class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-t from-ink/30 via-ink/8 to-ink/12"></div>
    {{-- Soft dark spotlight behind the text only, so the heading, intro and chips stay readable over the busy part of the photo while the edges stay bright. --}}
    <div class="pointer-events-none absolute inset-0 -z-10" style="background:radial-gradient(ellipse 62% 52% at 50% 50%, color-mix(in srgb, var(--color-ink) 38%, transparent) 0%, color-mix(in srgb, var(--color-ink) 20%, transparent) 55%, transparent 100%)"></div>
    <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-28 bg-gradient-to-b from-ink/30 to-transparent"></div>
    <div class="container-site max-w-5xl [text-shadow:0_2px_4px_rgb(0_0_0/0.4),0_6px_32px_rgb(0_0_0/0.65)]">
        @if ($d['eyebrow'])<p class="hero-in label-caps text-accent drop-shadow-md">{{ $d['eyebrow'] }}</p>@endif
        <h1 style="--d:120ms" class="hero-in mt-4 text-4xl font-semibold uppercase tracking-wide sm:text-6xl lg:text-7xl">{!! emphasize($d['heading'], 'text-accent') !!}</h1>
        @if ($d['text'])<p style="--d:240ms" class="hero-in mx-auto mt-5 max-w-2xl text-base font-medium text-white sm:text-lg">{{ $d['text'] }}</p>@endif
        <div style="--d:360ms" class="hero-in mt-8 flex flex-wrap justify-center gap-3">
            @if ($d['primary_label'])<x-button @click="$store.modal.show()">{{ $d['primary_label'] }}</x-button>@endif
            @if ($d['secondary_label'])<x-cta-button variant="outline" :label="$d['secondary_label']" :url="$d['secondary_url']" class="text-white" />@endif
        </div>
        @if (count($d['chips']))
        <div style="--d:480ms" class="hero-in mt-10">
            @if ($d['chips_label'])<p class="label-caps text-white/90">{{ $d['chips_label'] }}</p>@endif
            {{-- Destination chips as a looping marquee: the track holds two identical groups and slides left by one group (pauses on hover or focus). --}}
            <div class="mx-auto mt-4 [text-shadow:none]" style="width:100%;max-width:52rem">
            <div class="marquee" style="animation:none;--marquee-time:40s" role="region" aria-label="Destinations">
                <div class="marquee-track">
                    @foreach (range(1, 2) as $copy)
                        <ul class="marquee-group" style="gap:.75rem;padding-right:.75rem" @if ($copy === 2) aria-hidden="true" @endif>
                            @foreach (array_merge($d['chips'], $d['chips']) as $c)
                                <li><a @if ($copy === 2) tabindex="-1" @endif href="{{ str_starts_with((string) $c['url'], '/') ? url($c['url']) : ($c['url'] ?: url('/destinations')) }}" class="inline-flex min-h-[2.5rem] items-center whitespace-nowrap rounded-full border border-white/70 bg-ink/35 px-4 text-sm font-semibold text-white backdrop-blur-sm transition hover:border-accent hover:bg-accent hover:text-ink">{{ $c['label'] }}</a></li>
                            @endforeach
                        </ul>
                    @endforeach
                </div>
            </div>
            </div>
        </div>
        @endif
    </div>
</section>
