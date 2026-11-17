@extends('layouts.app')

@push('head')
    <link rel="preload" as="image" href="{{ asset('images/hero-video-poster.webp') }}" fetchpriority="high">
@endpush

@php
    $travellers = [
        ['First-time visitors', 'The essential Netherlands, paced so you see more and rush less.', 'first_time_visitors', 'Amsterdam canal houses and a canal boat on a bright morning'],
        ['Couples', 'Quiet canal towns, long dinners and time to simply enjoy each other.', 'couples', 'A couple dining at a candle-lit canal-side restaurant at dusk'],
        ['Families', 'Windmills, cheese markets and hands-on traditions the whole group will remember.', 'families', 'A family cycling past a windmill and green fields'],
        ['Culture lovers', 'Local traditions, living crafts and the stories behind them.', 'culture_lovers', 'A market vendor stacking wheels of Gouda cheese'],
        ['Off the beaten path', 'Lesser-known villages and local secrets away from the crowds.', 'off_the_beaten_path', 'A quiet, empty Dutch harbour village with colourful boats in morning mist'],
        ['Special occasions', 'Anniversaries and milestones, thoughtfully planned.', 'special_occasions', 'A table set with champagne on a terrace overlooking a canal at sunset'],
    ];
    $partners = ['Partner One', 'Partner Two', 'Partner Three', 'Partner Four', 'Partner Five', 'Partner Six'];
@endphp

@section('content')
{{-- HERO: background video (placeholder poster until public/videos/homepage_banner.mp4 exists) --}}
<section class="relative isolate flex min-h-[88vh] items-center overflow-hidden bg-brand-900 text-white">
    <x-video name="homepage_banner" poster="images/hero-video-poster.webp" alt="" class="absolute inset-0 -z-20 h-full w-full object-cover" priority />
    <div class="absolute inset-0 -z-10 bg-gradient-to-t from-ink/80 via-ink/40 to-ink/30"></div>
    <div class="container-site grid items-center gap-10 py-14 lg:grid-cols-[1.1fr_0.9fr] lg:py-20">
        <div>
            <p class="hero-in label-caps text-center text-accent lg:text-left">The Netherlands Collection</p>
            <h1 style="--d:120ms" class="hero-in mt-4 text-center text-4xl font-medium sm:text-5xl lg:text-left lg:text-7xl">{!! emphasize($page->h1, 'text-accent') !!}</h1>
            <p style="--d:240ms" class="hero-in mt-5 hidden max-w-xl text-base text-white/90 sm:mx-auto sm:block sm:text-center sm:text-lg lg:mx-0 lg:text-left">{{ $page->c('hero_sub') }}</p>
            <div style="--d:360ms" class="hero-in mt-7 hidden flex-wrap gap-3 sm:flex sm:justify-center lg:justify-start">
                <x-button @click="$store.modal.show()">Get a free quote</x-button>
                <x-button variant="outline" :href="route('packages.index')" class="text-white">Explore packages</x-button>
            </div>
            <ul style="--d:480ms" class="hero-in mt-8 hidden flex-wrap gap-x-6 gap-y-2 text-sm font-semibold sm:flex sm:justify-center lg:justify-start" aria-label="Why book with us">
                @foreach (data_get($page->content, 'badges', []) as $i => $badge)
                    <li class="flex items-center gap-2"><x-icon :name="['shield', 'map', 'star'][$i % 3]" class="h-5 w-5 shrink-0 text-accent" /> {{ $badge }}</li>
                @endforeach
            </ul>
        </div>
        <div style="--d:300ms" class="hero-in"><x-quiz :steps="$quizSteps" /></div>
    </div>
</section>

{{-- HIGHLIGHTS TICKER (selling points and themes; plain text, duplicated copy is hidden from screen readers) --}}
@php
    $ticker = [
        'Authentic Dutch Culture', 'Small Group Experiences', 'Hidden Gems & Local Secrets', 'Expertly Crafted Itineraries',
        'Windmills & Tulip Fields', 'Cheese Markets & Local Traditions', 'Flexible Itineraries', 'Free, No-Obligation Quote',
    ];
@endphp
<div class="overflow-hidden bg-brown py-3 text-white" role="region" aria-label="Why travel with us">
    <div class="marquee flex w-max gap-10">
        @foreach (range(1, 2) as $loop2)
            @foreach ($ticker as $item)
                <span class="label-caps whitespace-nowrap" @if ($loop2 === 2) aria-hidden="true" @endif>{{ $item }} <span class="text-accent">&#10022;</span></span>
            @endforeach
        @endforeach
    </div>
</div>

{{-- INTRO SPLIT --}}
<section id="intro" class="py-16 sm:py-24" aria-labelledby="intro-h">
    <div class="container-site grid items-center gap-10 lg:grid-cols-2">
        <div class="relative grid grid-cols-2 gap-4">
            <x-picture src="images/Intro_image_split.webp" alt="A quiet Dutch canal town at dawn with gabled brick houses reflected in still water" :width="900" :height="1100" class="aspect-[4/5] w-full rounded-2xl object-cover shadow-card" data-reveal="left" />
            <x-picture src="images/Intro_image_split1.webp" alt="A traditional windmill beside rows of tulips at golden hour" :width="900" :height="1100" class="mt-10 aspect-[4/5] w-full rounded-2xl object-cover shadow-card" data-reveal="zoom" style="--d:150ms" />
            <div class="float-slow absolute bottom-4 left-4 rounded-xl bg-accent px-4 py-3 text-sm font-bold text-ink shadow-card">Free, no-obligation quote</div>
        </div>
        <div data-reveal="right">
            <p class="label-caps text-rust">Why book with us</p>
            <h2 id="intro-h" class="mt-3 text-3xl font-medium sm:text-4xl lg:text-5xl">The real Netherlands, <em class="text-rust">crafted around you.</em></h2>
            <ol class="mt-8 space-y-6">
                @foreach (data_get($page->content, 'why', []) as $i => $w)
                    <li class="flex gap-4" data-reveal="up" style="--d:{{ $i * 120 }}ms">
                        <span class="font-display text-3xl text-accent-600">0{{ $i + 1 }}</span>
                        <div><h3 class="text-xl font-medium">{{ $w['title'] }}</h3><p class="mt-1 text-sm leading-relaxed text-ink/75">{{ $w['text'] }}</p></div>
                    </li>
                @endforeach
            </ol>
            <p class="mt-8"><x-button @click="$store.modal.show()">Get a free quote</x-button></p>
        </div>
    </div>
</section>

{{-- SIGNATURE JOURNEYS: tabs by tour type --}}
@if ($featured->isNotEmpty())
<section class="bg-sand py-16 sm:py-24" aria-labelledby="packages" x-data="{ tab: 'all' }">
    <div class="container-site">
        <x-section-heading data-reveal="up" eyebrow="Signature journeys" :title="$page->c('packages_heading')" id="packages" />
        <div class="mb-8 grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:justify-center" role="tablist">
            <button type="button" role="tab" :aria-selected="tab === 'all'" class="btn w-full !px-3 !py-2 !text-[0.7rem] sm:w-auto sm:!px-6 sm:!text-[0.8rem]" :class="tab === 'all' ? 'btn-secondary' : 'btn-outline text-brand-700'" @click="tab = 'all'">All</button>
            @foreach ($tourTypes as $t)
                <button type="button" role="tab" :aria-selected="tab === '{{ $t->slug }}'" class="btn w-full !px-3 !py-2 !text-[0.7rem] sm:w-auto sm:!px-6 sm:!text-[0.8rem]" :class="tab === '{{ $t->slug }}' ? 'btn-secondary' : 'btn-outline text-brand-700'" @click="tab = '{{ $t->slug }}'">{{ $t->name }}</button>
            @endforeach
        </div>
        <x-slider class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($featured as $p)
                <div x-show="tab === 'all' || tab === '{{ $p->tourType?->slug }}'" x-transition.opacity>@include('partials.package-card', ['package' => $p])</div>
            @endforeach
        </x-slider>
        <p class="mt-10 text-center"><x-button variant="secondary" :href="route('packages.index')">See all packages</x-button></p>
    </div>
</section>
@endif

{{-- FULL-WIDTH FEATURE BAND --}}
<section class="relative isolate overflow-hidden py-24 text-white sm:py-32" aria-labelledby="band">
    <x-picture src="images/feature_band.webp" alt="Dutch countryside at sunrise with windmills along a river" :width="1800" :height="900" class="kenburns absolute inset-0 -z-20 h-full w-full object-cover" />
    <div class="absolute inset-0 -z-10 bg-gradient-to-r from-ink/80 via-ink/50 to-transparent"></div>
    <div class="container-site">
        <div class="max-w-xl" data-reveal="left">
            <p class="label-caps text-accent">Our promise</p>
            <h2 id="band" class="mt-3 text-4xl font-medium sm:text-5xl lg:text-6xl">Authentic, personal, unhurried</h2>
            <ul class="mt-6 space-y-2 font-semibold">
                @foreach (['Authentic Dutch culture, not a checklist', 'Small groups with flexible itineraries', 'Hidden gems beyond the tourist hotspots', 'Expertly crafted routes and attractions'] as $li)
                    <li class="flex items-center gap-2"><x-icon name="check" class="h-5 w-5 text-accent" /> {{ $li }}</li>
                @endforeach
            </ul>
            <div class="mt-8 flex flex-wrap gap-3">
                <x-button @click="$store.modal.show()">Plan my trip</x-button>
                <x-button variant="outline" :href="route('packages.index')" class="text-white">See packages</x-button>
            </div>
        </div>
    </div>
</section>

{{-- TRAVEL STYLES --}}
<section class="py-16 sm:py-24" aria-labelledby="tours">
    <div class="container-site">
        <x-section-heading data-reveal="up" eyebrow="Tour types" :title="$page->c('tours_heading')" id="tours" />
        <x-slider class="grid gap-6 md:grid-cols-3">
            @foreach ($tourTypes as $i => $t)
                <article class="group flex flex-col" data-reveal="up" style="--d:{{ $i * 120 }}ms">
                    <div class="overflow-hidden rounded-2xl">
                        <x-picture :src="$t->image" :alt="$t->image_alt" :width="960" :height="640" class="aspect-[3/2] w-full object-cover transition-transform duration-500 group-hover:scale-105" sizes="(min-width:768px) 33vw, 100vw" />
                    </div>
                    <p class="label-caps mt-5 text-rust">0{{ $i + 1 }}</p>
                    <h3 class="mt-1 text-2xl font-medium">{{ $t->name }}</h3>
                    <p class="mt-2 flex-1 text-sm leading-relaxed text-ink/75">{{ $t->summary }}</p>
                    <p class="mt-4 flex gap-5 text-sm font-bold">
                        <button type="button" class="text-brand-700 underline underline-offset-4 hover:text-rust" @click="$store.modal.show()">Get a Quote</button>
                        <a href="{{ route('contact', ['topic' => $t->slug]) }}" class="text-brand-700 underline underline-offset-4 hover:text-rust">Ask Us a Question</a>
                    </p>
                </article>
            @endforeach
        </x-slider>
    </div>
</section>

{{-- VIDEO FEATURE --}}
<section class="bg-brown py-16 text-white sm:py-24" aria-labelledby="video-h">
    <div class="container-site grid items-center gap-10 lg:grid-cols-[0.8fr_1.2fr]">
        <div data-reveal="left">
            <p class="label-caps text-accent">Watch</p>
            <h2 id="video-h" class="mt-3 text-3xl font-medium sm:text-4xl lg:text-5xl">See the Netherlands <em class="text-accent">before you go</em></h2>
            <p class="mt-4 text-white/85">Windmills, tulip fields, cheese markets and hidden corners, seen the small-group way.</p>
        </div>
        <div class="relative overflow-hidden rounded-2xl shadow-card" x-data="{ playing: false }" data-reveal="zoom" style="--d:150ms">
            <x-video name="homepage_banner" poster="images/hero-video-poster.webp" alt="Travellers walking along a canal-side street lined with historic houses" :background="false" class="aspect-video w-full object-cover" />
        </div>
    </div>
</section>

{{-- JOURNEYS BY TRAVELLER --}}
<section class="bg-sand py-16 sm:py-24" aria-labelledby="trav">
    <div class="container-site">
        <x-section-heading data-reveal="up" eyebrow="Journeys by traveller" title="Find the journey that *fits you*" id="trav" />
        {{-- Phone/tablet: swipe cards. Desktop: a strip of tall photo panels; the hovered (or first) panel opens up. --}}
        <x-slider class="expand-strip grid gap-6 sm:grid-cols-2">
            @foreach ($travellers as $i => [$title, $blurb, $img, $alt])
                <article class="strip-panel group relative isolate flex min-h-72 flex-col justify-end overflow-hidden rounded-2xl p-6 text-white shadow-card" data-reveal="up" style="--d:{{ ($i % 3) * 120 }}ms" tabindex="0">
                    <x-picture :src="'images/'.$img.'.webp'" :alt="$alt" :width="2000" :height="1116" class="absolute inset-0 -z-20 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" />
                    <div class="absolute inset-0 -z-10 bg-gradient-to-t from-ink/85 via-ink/30 to-transparent"></div>
                    <p class="label-caps text-accent">0{{ $i + 1 }}</p>
                    <h3 class="strip-title mt-1 text-2xl font-medium">{{ $title }}</h3>
                    <div class="strip-more">
                        <p class="mt-1 text-sm text-white/85">{{ $blurb }}</p>
                        <button type="button" class="mt-3 self-start text-sm font-bold text-accent underline underline-offset-4" @click="$store.modal.show()">Plan this trip</button>
                    </div>
                </article>
            @endforeach
        </x-slider>
    </div>
</section>

{{-- HOW IT WORKS --}}
<section class="py-16 sm:py-24" aria-labelledby="how">
    <div class="container-site">
        <x-section-heading data-reveal="up" eyebrow="Simple" :title="$page->c('how_heading')" id="how" />
        <x-slider tag="ol" class="grid gap-8 md:grid-cols-3">
            @foreach (data_get($page->content, 'steps', []) as $i => $s)
                <li class="text-center" data-reveal="up" style="--d:{{ $i * 150 }}ms">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-brown font-display text-2xl text-accent">{{ $i + 1 }}</span>
                    <h3 class="mt-4 text-xl font-medium">{{ $s['title'] }}</h3>
                    <p class="mx-auto mt-2 max-w-xs text-sm text-ink/75">{{ $s['text'] }}</p>
                </li>
            @endforeach
        </x-slider>
    </div>
</section>

{{-- TESTIMONIALS --}}
@if ($testimonials->isNotEmpty())
<section class="bg-brand-900 py-16 text-white sm:py-24" aria-labelledby="reviews">
    <div class="container-site">
        <x-section-heading data-reveal="up" eyebrow="Reviews" :title="$page->c('testimonials_heading')" light id="reviews" />
        <x-slider light class="grid gap-6 md:grid-cols-3">
            @foreach ($testimonials as $t)
                <figure class="relative rounded-2xl bg-white/10 p-6 pt-10 transition-transform duration-300 hover:-translate-y-1" data-reveal="up" style="--d:{{ $loop->index * 120 }}ms">
                    <span class="absolute left-5 top-1 font-display text-6xl leading-none text-accent" aria-hidden="true">&ldquo;</span>
                    <blockquote class="text-base leading-relaxed">{{ $t->quote }}</blockquote>
                    <figcaption class="mt-4 text-sm font-semibold text-accent">@if ($t->is_placeholder)<span class="label-caps mb-2 block text-[0.65rem] text-white/60">Sample review</span>@endif {{ $t->name }}@if ($t->location), <span class="font-normal text-white/75">{{ $t->location }}</span>@endif</figcaption>
                </figure>
            @endforeach
        </x-slider>
    </div>
</section>
@endif

{{-- GALLERY with lightbox. Fully editable in Admin > Pages > Home (checkbox at the end of the form); hidden when unticked. --}}
@if ($page->showGallery())
@php $gal = $page->gallery(); $galItems = array_values($gal['items']); @endphp
<section class="py-16 sm:py-24" aria-labelledby="gal" x-data="{ open: null }" @keydown.escape.window="open = null">
    <div class="container-site">
        <x-section-heading data-reveal="up" :eyebrow="$gal['eyebrow'] ?: null" :title="$gal['title']" id="gal" />
        {{-- Balanced mosaic: 4 columns, pattern repeats every 6 photos. Row 1 = wide + 2 small, row 2 = 2 small + wide. --}}
        <div class="gallery-grid grid auto-rows-[11rem] grid-cols-2 gap-3 sm:auto-rows-[14rem] md:auto-rows-[17rem] md:grid-cols-4 md:gap-4">
            @foreach ($galItems as $i => $g)
                <button type="button" class="gallery-tile group relative isolate overflow-hidden rounded-2xl text-left shadow-card {{ in_array($i % 6, [0, 5]) ? 'col-span-2' : '' }}" data-reveal="zoom" style="--d:{{ ($i % 6) * 90 }}ms" @click="open = {{ $i }}" aria-label="Enlarge: {{ $g['caption'] ?? '' }}">
                    <x-picture :src="$g['image']" :alt="$g['alt'] ?? ''" :width="2000" :height="1116" class="absolute inset-0 -z-10 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" />
                    <span class="absolute inset-0 bg-gradient-to-t from-ink/75 via-transparent to-transparent opacity-90 transition-opacity group-hover:opacity-100"></span>
                    <span class="absolute inset-x-0 bottom-0 flex items-end justify-between gap-2 p-4">
                        <span><span class="label-caps block text-accent">{{ sprintf('%02d', $i + 1) }}</span><span class="font-display text-lg leading-tight text-white sm:text-xl">{{ $g['caption'] ?? '' }}</span></span>
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white/90 text-ink opacity-0 transition-opacity group-hover:opacity-100 group-focus-visible:opacity-100" aria-hidden="true"><svg viewBox="0 0 24 24" class="h-4 w-4 fill-none stroke-current" stroke-width="2"><path d="M4 9V4h5M20 9V4h-5M4 15v5h5M20 15v5h-5"/></svg></span>
                    </span>
                </button>
            @endforeach
        </div>
        <div x-show="open !== null" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center bg-ink/90 p-4" @click="open = null" role="dialog" aria-modal="true" aria-label="Photo viewer">
            @foreach ($galItems as $i => $g)
                <figure x-show="open === {{ $i }}" class="max-w-4xl" @click.stop>
                    <img src="{{ asset($g['image']) }}" loading="lazy" decoding="async" alt="{{ $g['alt'] ?? '' }}" class="max-h-[80vh] w-full rounded-xl object-contain">
                    <figcaption class="mt-3 text-center text-sm text-white">{{ $g['caption'] ?? '' }}</figcaption>
                </figure>
            @endforeach
            <button type="button" class="btn btn-primary absolute right-4 top-4 !py-2" @click="open = null">Close</button>
        </div>
        <div class="mt-12 text-center" data-reveal="up">
            <p class="font-display text-2xl">Seen enough to start planning?</p>
            <p class="mt-4"><x-button @click="$store.modal.show()">Get a free quote</x-button></p>
        </div>
    </div>
</section>
@endif

{{-- ABOUT TEASER --}}
<section class="bg-sand py-16 sm:py-20" aria-labelledby="about-t">
    <div class="container-site max-w-3xl text-center" data-reveal="up">
        <h2 id="about-t" class="text-3xl font-medium sm:text-4xl">Plan with <em class="text-rust">peace of mind</em></h2>
        <p class="mt-4 text-ink/75">We design thoughtful routes with carefully selected attractions, in small groups, with room to explore. Real people, clear quotes, no pressure. [CONFIRM: company story]</p>
        <div class="mt-6 flex flex-wrap justify-center gap-3">
            <x-button variant="secondary" :href="route('about')">About us</x-button>
            <x-button variant="secondary" :href="route('contact')">Contact us</x-button>
        </div>
    </div>
</section>

{{-- FAQ --}}
<section class="py-16 sm:py-24" aria-labelledby="faq">
    <div class="container-site max-w-3xl">
        <x-section-heading data-reveal="up" eyebrow="FAQ" :title="$page->c('faq_heading')" id="faq" />
        <div class="space-y-3" data-reveal="up">
            @foreach ($faqs as $f)<x-faq-item :question="$f->question">{{ $f->answer }}</x-faq-item>@endforeach
        </div>
    </div>
</section>

{{-- TRAVEL JOURNAL (latest blog posts, managed in Admin > Blog) --}}
@if ($posts->isNotEmpty())
<section class="bg-sand py-16 sm:py-24" aria-labelledby="journal">
    <div class="container-site">
        <x-section-heading data-reveal="up" eyebrow="Travel journal" title="Guides and *inspiration*" id="journal" />
        <x-slider class="mx-auto grid max-w-4xl gap-6 md:grid-cols-2">
            @foreach ($posts as $post)
                @include('partials.blog-card', ['post' => $post])
            @endforeach
        </x-slider>
        <p class="mt-10 text-center"><x-button variant="secondary" :href="route('blog.index')">Read all articles</x-button></p>
    </div>
</section>
@endif

{{-- FINAL CTA --}}
<section class="relative isolate overflow-hidden py-20 text-center text-white sm:py-28" aria-labelledby="cta">
    <x-picture src="images/fina_cta_bg.webp" alt="Tulip fields and a distant windmill at sunset" :width="2000" :height="1116" class="kenburns absolute inset-0 -z-20 h-full w-full object-cover" />
    <div class="absolute inset-0 -z-10 bg-brand-900/75"></div>
    <div class="container-site max-w-3xl" data-reveal="up">
        <h2 id="cta" class="text-3xl font-medium sm:text-5xl">{{ $page->c('cta_heading') }}</h2>
        <p class="mt-4 text-lg text-white/90">{{ $page->c('cta_text') }}</p>
        <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
            <x-button @click="$store.modal.show()">Start Your Adventure</x-button>
            @if (setting('phone'))<x-button variant="outline" :href="'tel:'.preg_replace('/[^+\d]/', '', setting('phone'))">Call {{ setting('phone') }}</x-button>@endif
        </div>
    </div>
</section>
@endsection
