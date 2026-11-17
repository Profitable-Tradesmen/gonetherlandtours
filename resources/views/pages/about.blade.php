@extends('layouts.app')
{{-- About Us: centred-hero layout (season picker, feature cards, split intro, dark why-us band). Uses only site tokens/components. Copy lives here; business facts stay [CONFIRM: ...] (docs/reference/CONTENT_TO_CONFIRM.md). --}}
@push('head')
    <link rel="preload" as="image" href="{{ asset('images/hero.webp') }}" fetchpriority="high">
@endpush

@php
    $seasons = [
        ['spring', 'Spring', 'Tulip season'],
        ['summer', 'Summer', 'Long days, coast and canals'],
        ['autumn', 'Autumn', 'Golden light, quiet cities'],
        ['winter', 'Winter', 'Christmas markets, cosy canals'],
    ];
    $features = [
        ['map', 'Tailor-Made Trips', 'Every itinerary is built around your dates, pace and interests, not a one-size-fits-all template.'],
        ['car', 'Your Way to Travel', 'Choose self drive, chauffeur driven or coach touring, whichever suits you best.'],
        ['shield', 'Built for US Travellers', 'Prices in EUR, plain-English planning and help with the things first-time visitors ask about, like driving rules and entry requirements.'],
        ['phone', 'Support When You Need It', 'A real travel specialist plans your trip and is there to answer questions before you go. [CONFIRM: support hours / in-trip support]'],
    ];
    $bullets = [
        'Tailor-made Netherlands itineraries',
        'Self drive, chauffeur driven and coach tours',
        'Handpicked hotels, from canal-house boutiques to countryside stays [CONFIRM: accommodation standard]',
        'Timed-entry planning for top sights such as Keukenhof and the Rijksmuseum',
        'Pre-trip guidance on driving, travel documents and packing',
        'Support from a Netherlands travel specialist [CONFIRM: hours]',
    ];
    $why = [
        ['The Best of the Netherlands', 'From Amsterdam and the Dutch coast to Utrecht, Delft, Haarlem, Rotterdam and the villages of Giethoorn. We balance famous highlights with quieter places.'],
        ['Chosen for the Season', 'Tulip season, summer by the sea, autumn colour or Christmas markets. We plan around what the Netherlands looks like when you are there. [CONFIRM: tulip season dates]'],
        ['Three Ways to Travel', 'Drive yourself with a route and bookings in place, relax with a private chauffeur, or join a coach tour. Same destinations, your choice of pace.'],
        ['Hassle-Free Planning', 'We help organise the details, including accommodation, transfers, route planning and timed entries, so you do not have to piece it together yourself.'],
        ['Local Knowledge', 'Dutch trips come with practical details: when to book the Anne Frank House, how cycle lanes work for drivers, and where to go when the tulip fields are crowded.'],
        ['Clear, Honest Quotes', 'Tell us what you want and we will send a tailored quote with no obligation. Prices are in EUR. [CONFIRM: what is included in a standard quote]'],
    ];
@endphp

@section('content')
{{-- HERO --}}
<section class="relative isolate overflow-hidden bg-brand-900 py-16 text-center text-white sm:py-24">
    <x-picture src="images/hero.webp" alt="Dutch countryside with a windmill and tulip fields at sunrise" :width="1920" :height="1080" class="absolute inset-0 -z-20 h-full w-full object-cover" priority />
    <div class="absolute inset-0 -z-10 bg-gradient-to-t from-ink/80 via-ink/50 to-ink/40"></div>
    <div class="container-site max-w-4xl">
        <nav aria-label="Breadcrumb" class="mb-4 text-xs text-white/70"><a href="{{ route('home') }}" class="hover:text-accent">Home</a> / <span>{{ $page->title }}</span></nav>
        <p class="label-caps text-accent">About GoNetherlandTours</p>
        <h1 class="mt-4 text-4xl font-medium sm:text-6xl">About Us</h1>
        <p class="mx-auto mt-5 max-w-2xl text-base text-white/90 sm:text-lg">At GoNetherlandTours, we plan Netherlands holidays for American travellers who want the real thing: tulip fields in bloom, windmills along the polders, canal-side towns and world-class museums. Our travel specialists handle the planning, the drivers and the logistics, so you can enjoy every day.</p>
        <p class="mt-7"><x-button @click="$store.modal.show()">Get Your Free Netherlands Quote</x-button></p>

        <div class="mx-auto mt-10 max-w-3xl rounded-2xl border-2 border-accent bg-white p-5 text-ink shadow-card">
            <p class="text-sm font-semibold">When would you like to visit the Netherlands?</p>
            <ul class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach ($seasons as [$icon, $name, $note])
                    <li><button type="button" @click="$store.modal.show()" class="flex h-full w-full flex-col items-center gap-1 rounded-xl border border-sand-200 p-3 text-center transition hover:border-accent hover:bg-sand">
                        <x-icon :name="$icon" class="h-7 w-7 text-brand-700" />
                        <span class="text-sm font-bold">{{ $name }}</span>
                        <span class="text-xs text-ink/70">{{ $note }}</span>
                    </button></li>
                @endforeach
            </ul>
        </div>

        <ul class="mt-8 flex flex-wrap justify-center gap-x-6 gap-y-2 text-sm font-semibold" aria-label="Why book with us">
            @foreach (['Local Netherlands specialists', 'Tailor-made itineraries', 'Self drive, chauffeur and coach options', '[CONFIRM: traveller reviews badge]'] as $i => $badge)
                <li class="flex items-center gap-2"><x-icon :name="['shield', 'map', 'car', 'star'][$i]" class="h-5 w-5 shrink-0 text-accent" /> {{ $badge }}</li>
            @endforeach
        </ul>
    </div>
</section>

{{-- FEATURE CARDS --}}
<section class="bg-brand-900 py-12 text-white sm:py-16" aria-label="What sets us apart">
    <div class="container-site">
        <x-slider class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($features as [$icon, $title, $text])
                <div class="rounded-2xl bg-white p-6 text-center text-ink shadow-card">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-sand text-brand-700"><x-icon :name="$icon" class="h-7 w-7" /></span>
                    <h2 class="mt-3 font-sans text-base font-bold">{{ $title }}</h2>
                    <p class="mt-2 text-sm leading-relaxed text-ink/75">{{ $text }}</p>
                </div>
            @endforeach
        </x-slider>
    </div>
</section>

{{-- INTRO SPLIT --}}
<section class="py-16 sm:py-24" aria-labelledby="intro-h">
    <div class="container-site grid gap-10 lg:grid-cols-2">
        <div>
            <x-picture src="images/Intro_image_split.webp" alt="A quiet Dutch canal town at dawn with gabled brick houses reflected in still water" :width="900" :height="1100" class="aspect-[4/3] w-full rounded-2xl object-cover shadow-card" />
            <h2 id="intro-h" class="mt-8 text-3xl font-medium sm:text-4xl">Netherlands Journeys <em class="text-rust">Designed for Discovery</em></h2>
            <p class="mt-4 leading-relaxed text-ink/80">At GoNetherlandTours, we believe a great trip is more than a list of sights. The Netherlands is compact, beautiful and easy to love: you can wake up in a canal house in Amsterdam, spend the afternoon among the windmills of Kinderdijk and finish the day in a Delft caf&eacute;. We design routes that make the most of that variety without rushing you. We plan self drive, chauffeur driven and coach tours for American travellers, with clear pricing and a specialist who knows the country.</p>
        </div>
        <div>
            <x-picture src="images/Intro_image_split1.webp" alt="A traditional windmill beside rows of tulips at golden hour" :width="900" :height="1100" class="aspect-[4/3] w-full rounded-2xl object-cover shadow-card" />
            <h2 class="mt-8 text-3xl font-medium sm:text-4xl">Discover the Netherlands Your Way</h2>
            <p class="mt-4 leading-relaxed text-ink/80">From the tulip fields of the Bollenstreek to the Dutch Golden Age cities, we shape trips around what matters to you. Whether it is your first visit or a return trip to see the places you missed, we will build something that fits.</p>
            <ul class="mt-5 space-y-2 text-sm font-semibold text-brand-700">
                @foreach ($bullets as $b)
                    <li class="flex gap-2"><x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0 text-rust" /> <span>{{ $b }}</span></li>
                @endforeach
            </ul>
        </div>
    </div>
</section>

{{-- WHY TOUR WITH US --}}
<section class="bg-brand-900 py-16 text-white sm:py-24" aria-labelledby="why-h">
    <div class="container-site grid items-center gap-10 lg:grid-cols-[1.2fr_0.8fr]">
        <div>
            <h2 id="why-h" class="text-3xl font-medium sm:text-5xl">Why Tour With GoNetherlandTours?</h2>
            <ul class="mt-8 space-y-6">
                @foreach ($why as [$title, $text])
                    <li>
                        <h3 class="flex items-center gap-2 font-sans text-lg font-bold"><x-icon name="check" class="h-5 w-5 text-accent" /> {{ $title }}</h3>
                        <p class="mt-1 text-sm leading-relaxed text-white/85">{{ $text }}</p>
                    </li>
                @endforeach
            </ul>
        </div>
        <x-picture src="images/feature_band.webp" alt="Dutch countryside at sunrise with windmills along a river" :width="900" :height="1100" class="hidden aspect-[4/5] w-full rounded-2xl object-cover shadow-card lg:block" />
    </div>
</section>

{{-- MEET THE TEAM. Placeholders only: replace the [CONFIRM] names, roles and bios (and add photos) in this view. Never invent staff. --}}
<section class="py-16 sm:py-24" aria-labelledby="team-h">
    <div class="container-site">
        <x-section-heading data-reveal="up" eyebrow="Our people" title="Meet the *team*" id="team-h" />
        <x-slider class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ([1, 2, 3] as $n)
                <figure class="rounded-2xl bg-sand p-6 text-center" data-reveal="up" style="--d:{{ ($n - 1) * 120 }}ms">
                    <div class="mx-auto flex h-28 w-28 items-center justify-center rounded-full bg-brand-900 font-display text-3xl text-accent" aria-hidden="true">{{ $n }}</div>
                    <figcaption class="mt-4">
                        <p class="font-display text-2xl">[CONFIRM: team member {{ $n }} name]</p>
                        <p class="label-caps mt-1 text-xs text-rust">[CONFIRM: role]</p>
                        <p class="mt-3 text-sm leading-relaxed text-ink/75">[CONFIRM: short bio and why they love the Netherlands. Sample text only.]</p>
                    </figcaption>
                </figure>
            @endforeach
        </x-slider>
    </div>
</section>

{{-- TESTIMONIALS (database; sample reviews are labelled until real ones are added) --}}
@if ($testimonials->isNotEmpty())
<section class="bg-sand py-16 sm:py-24" aria-labelledby="reviews">
    <div class="container-site">
        <x-section-heading title="What Our Travellers Say" id="reviews" />
        <x-slider class="grid gap-6 md:grid-cols-3">
            @foreach ($testimonials as $t)
                <figure class="relative rounded-2xl bg-white p-6 pt-10 shadow-card">
                    <span class="absolute left-5 top-1 font-display text-6xl leading-none text-accent" aria-hidden="true">&ldquo;</span>
                    <blockquote class="text-base leading-relaxed">{{ $t->quote }}</blockquote>
                    <figcaption class="mt-4 text-sm font-semibold text-brand-700">@if ($t->is_placeholder)<span class="label-caps mb-2 block text-[0.65rem] text-ink/60">Sample review</span>@endif {{ $t->name }}@if ($t->location), <span class="font-normal text-ink/70">{{ $t->location }}</span>@endif</figcaption>
                </figure>
            @endforeach
        </x-slider>
    </div>
</section>
@endif

{{-- FINAL CTA (lead capture uses the shared enquiry modal) --}}
<section class="relative isolate overflow-hidden py-20 text-center text-white sm:py-28" aria-labelledby="cta">
    <x-picture src="images/fina_cta_bg.webp" alt="Tulip fields and a distant windmill at sunset" :width="2000" :height="1116" class="absolute inset-0 -z-20 h-full w-full object-cover" />
    <div class="absolute inset-0 -z-10 bg-brand-900/75"></div>
    <div class="container-site max-w-3xl">
        <h2 id="cta" class="text-3xl font-medium sm:text-5xl">Planning your first Dutch trip?</h2>
        <p class="mt-4 text-lg text-white/90">Answer a few quick questions and we will help you work out the right season, route and style of travel.</p>
        <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
            <x-button @click="$store.modal.show()">Get a free quote</x-button>
            @if (setting('phone'))<x-button variant="outline" :href="'tel:'.preg_replace('/[^+\d]/', '', setting('phone'))">Talk to a Netherlands travel specialist: {{ setting('phone') }}</x-button>@endif
        </div>
    </div>
</section>
@endsection
