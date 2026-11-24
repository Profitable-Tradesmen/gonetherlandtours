@push('head')
    @if ($d['image'])<link rel="preload" as="image" href="{{ asset_v($d['image']) }}" @if ($sets = static_srcset($d['image'])) imagesrcset="{{ $sets }}" imagesizes="100vw" @endif fetchpriority="high">@endif
@endpush
{{-- Centered photo hero (reference: goeurotours.com): caps label and heading, one line of intro, two buttons and a row of destination chips. --}}
<section class="hero-pad relative isolate flex min-h-[88vh] items-center overflow-hidden bg-brand-900 text-center text-white {{ ! empty($overlayHeader) ? 'pb-16 pt-28 sm:pb-24 sm:pt-36' : 'py-16 sm:py-24' }}">
    @if ($d['image'])<x-picture :src="$d['image']" :alt="$d['image_alt']" :width="1920" :height="1080" priority sizes="100vw" class="absolute inset-0 -z-20 h-full w-full object-cover" />@endif
    {{-- Home page hero overlay (golden-hour grade, ink scrim, top darkening), with the dark edge mirrored so BOTH sides match (the text is centered). --}}
    <div class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-t from-accent-600/75 via-rust/35 to-brand-900/45 mix-blend-soft-light"></div>
    <div class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-t from-accent/30 via-transparent to-transparent"></div>
    <div class="pointer-events-none absolute inset-0 -z-10 bg-ink/30 lg:bg-ink/8"></div>
    {{-- Corners: lighter, with a warm sunset glow (amber/rose) instead of a dark edge. --}}
    <div class="pointer-events-none absolute inset-0 -z-10" style="background:radial-gradient(ellipse 45% 55% at 0% 0%, color-mix(in srgb, var(--color-accent) 38%, transparent), transparent 70%), radial-gradient(ellipse 45% 55% at 100% 0%, color-mix(in srgb, var(--color-accent) 38%, transparent), transparent 70%), radial-gradient(ellipse 45% 55% at 0% 100%, color-mix(in srgb, var(--color-rust) 42%, transparent), transparent 70%), radial-gradient(ellipse 45% 55% at 100% 100%, color-mix(in srgb, var(--color-rust) 42%, transparent), transparent 70%)"></div>
    {{-- Soft dark spotlight behind the text only, so the heading, intro and chips stay readable over the busy part of the photo while the edges stay bright. --}}
    <div class="pointer-events-none absolute inset-0 -z-10" style="background:radial-gradient(ellipse 58% 32% at 50% 42%, color-mix(in srgb, var(--color-ink) 40%, transparent) 0%, color-mix(in srgb, var(--color-ink) 22%, transparent) 60%, transparent 100%), radial-gradient(ellipse 62% 52% at 50% 50%, color-mix(in srgb, var(--color-ink) 34%, transparent) 0%, color-mix(in srgb, var(--color-ink) 18%, transparent) 55%, transparent 100%)"></div>
    <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-44 bg-gradient-to-b from-ink/60 via-ink/25 to-transparent"></div>
    <div class="pointer-events-none absolute inset-x-0 bottom-0 -z-10 h-1/3 bg-gradient-to-t from-ink/55 to-transparent"></div>
    <div class="container-site [text-shadow:0_2px_4px_rgb(0_0_0/0.4),0_6px_32px_rgb(0_0_0/0.65)]"><div class="mx-auto max-w-5xl">
        @if ($d['eyebrow'])<p class="hero-in"><span class="label-caps inline-block rounded-full bg-ink/85 px-4 py-1.5 text-[0.8rem] font-bold [text-shadow:none]" style="color:#F2B743">{{ $d['eyebrow'] }}</span></p>@endif
        <h1 style="--d:120ms" class="hero-in mt-4 text-4xl font-semibold uppercase tracking-wide sm:text-6xl lg:text-7xl">{!! emphasize($d['heading'], 'text-accent') !!}</h1>
        @if ($d['text'])<p style="--d:240ms" class="hero-in mx-auto mt-5 max-w-2xl text-base font-medium text-white sm:text-lg">{{ $d['text'] }}</p>@endif
        <div style="--d:360ms" class="hero-in mt-8 flex flex-wrap justify-center gap-3">
            @if ($d['primary_label'])<x-button @click="$store.modal.show()">{{ $d['primary_label'] }}</x-button>@endif
            @if ($d['secondary_label'])<x-cta-button variant="outline" :label="$d['secondary_label']" :url="$d['secondary_url']" class="text-white" />@endif
        </div>
        @if (count($d['chips']))
        <div style="--d:480ms" class="hero-in mt-10">
            @if ($d['chips_label'])<p class="label-caps font-bold text-white [text-shadow:0_1px_3px_rgb(0_0_0/0.8)]">{{ $d['chips_label'] }}</p>@endif
            {{-- Destination chips: a simple centered row that wraps on small screens. --}}
            <ul class="mx-auto mt-4 flex max-w-3xl flex-wrap justify-center gap-2.5 [text-shadow:none]" aria-label="Destinations">
                @foreach ($d["chips"] as $c)
                    <li><a href="{{ str_starts_with((string) $c["url"], "/") ? url($c["url"]) : ($c["url"] ?: url("/destinations")) }}" class="inline-flex min-h-[2.5rem] items-center whitespace-nowrap rounded-full border border-white/70 bg-ink/35 px-4 text-sm font-semibold text-white backdrop-blur-sm transition hover:border-accent hover:bg-accent hover:text-ink">{{ $c["label"] }}</a></li>
                @endforeach
            </ul>
        </div>
        @endif
    </div></div>
</section>
