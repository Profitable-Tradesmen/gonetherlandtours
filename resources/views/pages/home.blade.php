@extends('layouts.app')

@push('head')
    <link rel="preload" as="image" href="{{ asset('images/hero.webp') }}" fetchpriority="high">
@endpush

@section('content')
{{-- 3. HERO --}}
<section class="relative isolate overflow-hidden bg-brand-900 text-white">
    <x-picture src="images/hero.webp" alt="" :width="1600" :height="1000" priority class="absolute inset-0 -z-20 h-full w-full object-cover" />
    <div class="absolute inset-0 -z-10 bg-gradient-to-r from-ink/85 via-ink/60 to-ink/30"></div>
    <div class="container-site grid items-center gap-10 py-12 sm:py-16 lg:grid-cols-[1.1fr_0.9fr] lg:py-20">
        <div>
            <p class="label-caps text-accent">Self Drive &middot; Chauffeur &middot; Coach Tours</p>
            <h1 class="mt-4 text-4xl font-medium sm:text-5xl lg:text-6xl">{{ $page->h1 }}</h1>
            <p class="mt-5 max-w-xl text-base text-white/90 sm:text-lg">{{ $page->c('hero_sub') }}</p>
            <ul class="mt-7 grid gap-3 sm:grid-cols-3" aria-label="Why book with us">
                @foreach (data_get($page->content, 'badges', []) as $i => $badge)
                    <li class="flex items-center gap-2 rounded-lg bg-white/10 px-3 py-2.5 text-sm font-semibold backdrop-blur">
                        <x-icon :name="['shield', 'map', 'star'][$i % 3]" class="h-5 w-5 shrink-0 text-accent" /> {{ $badge }}
                    </li>
                @endforeach
            </ul>
        </div>
        <div><x-quiz :steps="$quizSteps" /></div>
    </div>
</section>

{{-- 4. WHY TRAVELLERS CHOOSE US --}}
<section class="bg-sand py-16 sm:py-20" aria-labelledby="why">
    <div class="container-site">
        <x-section-heading eyebrow="Why us" :title="$page->c('why_heading')" id="why" />
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach (data_get($page->content, 'why', []) as $i => $w)
                <div class="rounded-2xl bg-white p-6 shadow-card">
                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-brand/15 text-brand-700"><x-icon :name="['map', 'heart', 'check', 'phone'][$i % 4]" /></span>
                    <h3 class="mt-4 text-xl font-medium">{{ $w['title'] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-ink/75">{{ $w['text'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- 5. TOUR TYPES --}}
<section class="py-16 sm:py-20" aria-labelledby="tours">
    <div class="container-site">
        <x-section-heading eyebrow="Tour types" :title="$page->c('tours_heading')" id="tours" />
        <div class="grid gap-6 md:grid-cols-3">
            @foreach ($tourTypes as $t)
                <article class="flex flex-col overflow-hidden rounded-2xl border border-sand-200 bg-white shadow-card">
                    <x-picture :src="$t->image" :alt="$t->image_alt" :width="960" :height="640" class="aspect-[3/2] w-full object-cover" sizes="(min-width:768px) 33vw, 100vw" />
                    <div class="flex flex-1 flex-col p-6">
                        <h3 class="text-2xl font-medium">{{ $t->name }}</h3>
                        <p class="mt-2 flex-1 text-sm leading-relaxed text-ink/75">{{ $t->summary }}</p>
                        <div class="mt-5 grid gap-2">
                            <x-button variant="primary" @click="$store.modal.show()">Get a Quote</x-button>
                            <x-button variant="secondary" :href="route('contact', ['topic' => $t->slug])">Ask Us a Question</x-button>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- 6. FEATURED PACKAGES --}}
@if ($featured->isNotEmpty())
<section class="bg-sand py-16 sm:py-20" aria-labelledby="packages">
    <div class="container-site">
        <x-section-heading eyebrow="Featured" :title="$page->c('packages_heading')" id="packages" />
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($featured as $p)@include('partials.package-card', ['package' => $p])@endforeach
        </div>
        <p class="mt-10 text-center"><x-button variant="secondary" :href="route('packages.index')">View all packages</x-button></p>
    </div>
</section>
@endif

{{-- 7. HOW IT WORKS --}}
<section class="py-16 sm:py-20" aria-labelledby="how">
    <div class="container-site">
        <x-section-heading eyebrow="Simple" :title="$page->c('how_heading')" id="how" />
        <ol class="grid gap-8 md:grid-cols-3">
            @foreach (data_get($page->content, 'steps', []) as $i => $s)
                <li class="text-center">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-brown font-display text-2xl text-accent">{{ $i + 1 }}</span>
                    <h3 class="mt-4 text-xl font-medium">{{ $s['title'] }}</h3>
                    <p class="mx-auto mt-2 max-w-xs text-sm text-ink/75">{{ $s['text'] }}</p>
                </li>
            @endforeach
        </ol>
    </div>
</section>

{{-- 8. TESTIMONIALS --}}
@if ($testimonials->isNotEmpty())
<section class="bg-brown py-16 text-white sm:py-20" aria-labelledby="reviews">
    <div class="container-site">
        <x-section-heading eyebrow="Reviews" :title="$page->c('testimonials_heading')" light id="reviews" />
        <div class="grid gap-6 md:grid-cols-3">
            @foreach ($testimonials as $t)
                <figure class="rounded-2xl bg-white/10 p-6">
                    <blockquote class="text-base leading-relaxed">&ldquo;{{ $t->quote }}&rdquo;</blockquote>
                    <figcaption class="mt-4 text-sm font-semibold text-accent">{{ $t->name }}@if ($t->location), <span class="font-normal text-white/75">{{ $t->location }}</span>@endif</figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- 9. FAQ (also output as FAQPage JSON-LD in the layout head) --}}
<section class="py-16 sm:py-20" aria-labelledby="faq">
    <div class="container-site max-w-3xl">
        <x-section-heading eyebrow="FAQ" :title="$page->c('faq_heading')" id="faq" />
        <div class="space-y-3">
            @foreach ($faqs as $f)<x-faq-item :question="$f->question">{{ $f->answer }}</x-faq-item>@endforeach
        </div>
    </div>
</section>

{{-- 10. FINAL CTA --}}
<section class="bg-brand-700 py-16 text-center text-white sm:py-20" aria-labelledby="cta">
    <div class="container-site max-w-3xl">
        <h2 id="cta" class="text-3xl font-medium sm:text-5xl">{{ $page->c('cta_heading') }}</h2>
        <p class="mt-4 text-lg text-white/90">{{ $page->c('cta_text') }}</p>
        <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
            <x-button @click="$store.modal.show()">Get My Free Quote</x-button>
            @if (setting('phone'))<x-button variant="outline" :href="'tel:'.preg_replace('/[^+\d]/', '', setting('phone'))">Call {{ setting('phone') }}</x-button>@endif
        </div>
    </div>
</section>
@endsection
