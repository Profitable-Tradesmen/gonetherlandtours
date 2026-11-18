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
            <button type="button" class="btn btn-primary" @click="$store.modal.show({ id: {{ $package->id }}, name: @js($package->title) })">Get a Free Quote</button>
        </div>
    </div>
</section>

<div class="container-site grid gap-10 py-10 lg:grid-cols-[1.4fr_1fr] lg:py-14">
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
            <section aria-labelledby="gal"><h2 id="gal" class="text-2xl font-medium sm:text-3xl">Gallery</h2>
                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">@foreach ($package->images->skip(1) as $img)<x-picture :media="$img" :alt="$img->alt" class="aspect-[4/3] w-full rounded-xl object-cover" sizes="(min-width:640px) 20vw, 50vw" />@endforeach</div>
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

            <button type="button" class="btn btn-primary mt-5 w-full" @click="$store.modal.show({ id: {{ $package->id }}, name: @js($package->title) })">Get a Free Quote</button>
            <a href="{{ route('contact') }}" class="btn btn-outline mt-3 w-full text-brand-900">Ask Us a Question</a>

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
            @foreach ($related as $rp)@include('partials.package-card', ['package' => $rp])@endforeach
        </x-slider>
        <p class="mt-8 text-center"><a href="{{ route('packages.index') }}" class="btn btn-secondary">View all packages</a></p>
    </div>
</section>
@endif
@endsection
