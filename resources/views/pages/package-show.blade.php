@extends('layouts.app')

@php
    $cover = $package->images->first();
    $fallback = $package->tourType?->image ?: 'images/tour-self-drive.webp';
@endphp

@push('head')
    @if ($cover)<link rel="preload" as="image" href="{{ $cover->url(960) }}" imagesrcset="{{ $cover->srcset() }}" imagesizes="100vw" fetchpriority="high">@endif
@endpush

@section('content')
{{-- Photo-led hero: the cover photo runs behind the (transparent) header, with the key facts as pills. --}}
<section class="relative isolate overflow-hidden bg-brown pb-10 pt-28 text-white sm:pb-14 sm:pt-36">
    @if ($cover)
        <x-picture :media="$cover" :alt="''" priority sizes="100vw" class="absolute inset-0 -z-20 h-full w-full object-cover" />
    @else
        <x-picture :src="$fallback" alt="" :width="960" :height="1430" priority sizes="100vw" class="absolute inset-0 -z-20 h-full w-full object-cover" />
    @endif
    <div class="absolute inset-0 -z-10 bg-gradient-to-t from-ink/85 via-ink/55 to-ink/40"></div>
    <div class="absolute inset-x-0 top-0 -z-10 h-40 bg-gradient-to-b from-ink/60 to-transparent"></div>
    <div class="container-site">
        <nav aria-label="Breadcrumb" class="mb-4 text-xs text-white/80">
            <a href="{{ route('home') }}" class="hover:text-accent">Home</a> / <a href="{{ route('packages.index') }}" class="hover:text-accent">Packages</a> / <span>{{ $package->title }}</span>
        </nav>
        <div class="flex flex-wrap gap-2 text-[0.72rem] font-bold uppercase tracking-wider">
            <span class="rounded-full bg-accent px-3 py-1.5 text-ink">{{ $package->nights }} {{ \Illuminate\Support\Str::plural('night', $package->nights) }}</span>
            @if ($package->tourType)<a href="{{ route('tour-types.show', $package->tourType) }}" class="rounded-full bg-white/20 px-3 py-1.5 text-white hover:bg-white/30">{{ $package->tourType->name }}</a>@endif
            @if ($package->destination)<a href="{{ route('destinations.show', $package->destination) }}" class="rounded-full border border-white/50 px-3 py-1.5 text-white hover:bg-white/10">{{ $package->destination->name }}</a>@endif
        </div>
        <h1 class="mt-4 max-w-3xl text-4xl font-medium sm:text-5xl">{{ $package->title }}</h1>
        <p class="mt-3 max-w-2xl text-white/90 sm:text-lg">{{ $package->summary }}</p>
        @if ($package->tourType?->style_note)<p class="mt-3 flex min-h-[2.5rem] items-start gap-2 text-sm font-semibold text-white sm:min-h-0 sm:items-center"><x-icon name="guide" class="mt-0.5 h-5 w-5 shrink-0 text-accent sm:mt-0" />{{ $package->tourType->style_note }}</p>@endif
        <div class="mt-6 flex flex-wrap items-end gap-x-6 gap-y-3">
            <div>
                <p class="label-caps text-[0.68rem] text-white/70">From</p>
                <p class="font-display text-3xl font-medium text-accent">{{ $package->priceLabel() }}<span class="ml-1 text-sm font-semibold text-accent/80">per person</span></p>
            </div>
            <button type="button" class="btn btn-primary" @click="$store.modal.show({ id: {{ $package->id }}, name: @js($package->title) })">Get a free quote</button>
        </div>
    </div>
</section>

<div class="container-site grid gap-10 py-10 lg:grid-cols-[1.4fr_1fr] lg:gap-16 lg:py-14">
    <div class="min-w-0 space-y-12">
        @if ($package->description)<div class="prose-site">{!! clean_html($package->description) !!}</div>@endif

        @if ($package->highlights)
            <section aria-labelledby="hl"><h2 id="hl" class="text-2xl font-medium sm:text-3xl">Highlights</h2>
                <ul class="mt-4 grid gap-3 sm:grid-cols-2">@foreach ($package->highlights as $h)
                    <li class="flex items-start gap-3 rounded-xl bg-sand p-4 font-semibold"><span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-accent text-ink"><x-icon name="check" class="h-4 w-4" /></span>{{ $h }}</li>
                @endforeach</ul>
            </section>
        @endif

        @if ($package->itinerary)
            <section aria-labelledby="it"><h2 id="it" class="text-2xl font-medium sm:text-3xl">Day by day</h2>
                <div class="mt-4 space-y-3">
                    @foreach ($package->itinerary as $n => $day)
                        @php $dayImg = $package->images->firstWhere('id', $day['image'] ?? null); @endphp
                        <details class="group overflow-hidden rounded-2xl border border-sand-200 bg-white shadow-sm" @if ($n === 0) open @endif>
                            <summary class="flex cursor-pointer list-none items-center gap-4 p-3 sm:p-4 [&::-webkit-details-marker]:hidden">
                                @if ($dayImg)
                                    <x-picture :media="$dayImg" :alt="''" class="h-16 w-16 shrink-0 rounded-xl object-cover" sizes="64px" />
                                @else
                                    <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-xl bg-brown font-display text-2xl text-accent" aria-hidden="true">{{ $n + 1 }}</span>
                                @endif
                                <span class="min-w-0 flex-1 font-display text-lg font-medium leading-snug sm:text-xl">{{ $day['title'] }}</span>
                                <x-icon name="chevron" class="h-5 w-5 shrink-0 text-rust transition-transform group-open:rotate-180" />
                            </summary>
                            <div class="grid gap-4 px-4 pb-5 sm:px-5 {{ $dayImg ? 'sm:grid-cols-[13rem_1fr]' : '' }}">
                                @if ($dayImg)<x-picture :media="$dayImg" :alt="$dayImg->alt" class="aspect-[4/3] w-full rounded-xl object-cover" sizes="(min-width:640px) 13rem, 100vw" />@endif
                                <p class="leading-relaxed text-ink/80">{{ $day['body'] ?? '' }}</p>
                            </div>
                        </details>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="grid gap-6 sm:grid-cols-2">
            @if ($package->inclusions)<section aria-labelledby="inc" class="rounded-2xl bg-sand p-5"><h2 id="inc" class="text-xl font-medium">Included</h2><ul class="mt-3 space-y-2 text-sm">@foreach ($package->inclusions as $i)<li class="flex gap-2"><x-icon name="check" class="h-5 w-5 shrink-0 text-brand-700" />{{ $i }}</li>@endforeach</ul></section>@endif
            @if ($package->exclusions)<section aria-labelledby="exc" class="rounded-2xl bg-sand p-5"><h2 id="exc" class="text-xl font-medium">Not included</h2><ul class="mt-3 space-y-2 text-sm">@foreach ($package->exclusions as $i)<li class="flex gap-2"><x-icon name="no" class="h-5 w-5 shrink-0 text-rust" />{{ $i }}</li>@endforeach</ul></section>@endif
        </div>

        @if ($package->images->count() > 1)
            @php
                $gallery = $package->images->skip(1)->values();
                $gn = $gallery->count();
                $reps = max(1, (int) ceil(6 / $gn));   // each group must be wider than the box, so the loop never shows a gap
                $big = $gallery->map(fn ($m) => ['src' => $m->url(), 'alt' => (string) $m->alt])->all();
            @endphp
            <section aria-labelledby="gal" x-data="gallerySlider(@js($big))">
                <h2 id="gal" class="text-2xl font-medium sm:text-3xl">Gallery</h2>
                <p class="sr-only">Photos slide slowly across; pause by hovering, and select a photo to enlarge it.</p>
                <div class="gslider mt-4" :class="open ? 'is-paused' : ''" style="--gslide-time: {{ $gn * $reps * 11 }}s">
                    <div class="gslider-track">
                        @foreach ([false, true] as $copy)
                            <div class="gslider-group" @if ($copy) aria-hidden="true" @endif>
                                @foreach (range(1, $reps) as $r)
                                    @foreach ($gallery as $k => $img)
                                        <button type="button" class="gslider-item group" @click="show({{ $k }}, $el)" aria-label="Enlarge photo: {{ $img->alt }}" @if ($copy || $r > 1) tabindex="-1" @endif>
                                            <x-picture :media="$img" :alt="($copy || $r > 1) ? '' : $img->alt" class="aspect-[4/3] w-full object-cover transition-transform duration-500 group-hover:scale-105" sizes="(min-width:640px) 17rem, 15rem" />
                                        </button>
                                    @endforeach
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Enlarged photo --}}
                <div x-show="open" x-cloak x-transition.opacity.duration.200ms x-trap.noscroll="open" role="dialog" aria-modal="true" aria-label="Photo viewer"
                     class="fixed inset-0 z-[80] flex items-center justify-center bg-ink/90 p-4 sm:p-8"
                     @click.self="close()" @keydown.escape.window="open && close()" @keydown.arrow-left.window="open && prev()" @keydown.arrow-right.window="open && next()">
                    <button type="button" @click="close()" class="absolute right-3 top-3 rounded-full bg-white/10 p-2.5 text-white hover:bg-white/25 sm:right-5 sm:top-5" aria-label="Close photo"><x-icon name="close" class="h-6 w-6" /></button>
                    <figure class="flex max-h-full max-w-5xl flex-col items-center" @click.self="close()">
                        <img :src="cur.src" :alt="cur.alt" class="max-h-[78vh] w-auto max-w-full rounded-xl object-contain shadow-2xl">
                        <figcaption class="mt-3 text-center text-sm text-white/85" x-text="cur.alt"></figcaption>
                    </figure>
                    @if ($gn > 1)
                        <button type="button" @click="prev()" class="absolute left-2 top-1/2 -translate-y-1/2 rounded-full bg-white/10 p-3 text-white hover:bg-white/25 sm:left-5" aria-label="Previous photo"><x-icon name="chevron" class="h-6 w-6 rotate-90" /></button>
                        <button type="button" @click="next()" class="absolute right-2 top-1/2 -translate-y-1/2 rounded-full bg-white/10 p-3 text-white hover:bg-white/25 sm:right-5" aria-label="Next photo"><x-icon name="chevron" class="h-6 w-6 -rotate-90" /></button>
                    @endif
                </div>
            </section>
        @endif

        @if ($package->faqs)
            <section aria-labelledby="pf"><h2 id="pf" class="text-2xl font-medium sm:text-3xl">Questions about this tour</h2>
                <div class="mt-4 space-y-3">@foreach ($package->faqs as $f)<x-faq-item :question="$f['question']" name="faq-package">{{ $f['answer'] }}</x-faq-item>@endforeach</div>
            </section>
        @endif
    </div>

    <aside class="lg:sticky lg:top-28 lg:self-start" aria-label="Get a quote">
        <div id="quote" class="rounded-2xl border border-sand-200 bg-white p-6 shadow-card">
            <p class="label-caps text-xs text-ink/60">From</p>
            <p class="font-display text-4xl font-medium text-brand-900">{{ $package->priceLabel() }}<span class="ml-1 text-sm font-semibold text-ink/60">per person</span></p>

            <dl class="mt-5 divide-y divide-sand-200 border-y border-sand-200 text-sm">
                <div class="flex justify-between py-2.5"><dt class="text-ink/60">Duration</dt><dd class="font-semibold">{{ $package->nights }} nights</dd></div>
                @if ($package->tourType)<div class="flex justify-between py-2.5"><dt class="text-ink/60">Tour type</dt><dd class="font-semibold">{{ $package->tourType->name }}</dd></div>@endif
                @if ($package->tourType?->style_note)<div class="flex justify-between gap-4 py-2.5"><dt class="shrink-0 text-ink/60">Travel style</dt><dd class="text-right font-semibold">{{ $package->tourType->style_note }}</dd></div>@endif
                @if ($package->destination)<div class="flex justify-between py-2.5"><dt class="text-ink/60">Destination</dt><dd class="font-semibold">{{ $package->destination->name }}</dd></div>@endif
            </dl>

            <button type="button" class="btn btn-primary mt-5 w-full" @click="$store.modal.show({ id: {{ $package->id }}, name: @js($package->title) })">Get a free quote</button>
            <a href="{{ route('contact') }}" class="btn btn-outline mt-3 w-full text-brand-900">Ask us a question</a>

            <ul class="mt-5 space-y-2 text-sm text-ink/80">
                <li class="flex gap-2"><x-icon name="check" class="h-5 w-5 shrink-0 text-rust" />Free, no-obligation quote</li>
                <li class="flex gap-2"><x-icon name="check" class="h-5 w-5 shrink-0 text-rust" />Tailored to your dates and group</li>
                <li class="flex gap-2"><x-icon name="check" class="h-5 w-5 shrink-0 text-rust" />A travel specialist replies personally</li>
            </ul>
        </div>
    </aside>
</div>

@include('partials.why-us')

@if ($related->isNotEmpty())
<section class="bg-sand py-14 sm:py-20" aria-labelledby="related-h">
    <div class="container-site">
        <x-section-heading eyebrow="More {{ $package->tourType->name }}" title="Related tour packages" id="related-h" />
        <x-slider class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($related as $rp)@include('partials.package-card-feature', ['package' => $rp])@endforeach
        </x-slider>
        <p class="mt-8 text-center"><a href="{{ route('packages.index') }}" class="btn btn-secondary">View all packages</a></p>
    </div>
</section>
@endif
@endsection
