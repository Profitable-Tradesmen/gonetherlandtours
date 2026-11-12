@extends('layouts.app')

@php
    $cover = $package->images->first();
    $fallback = $package->tourType?->image ?: 'images/tour-self-drive.webp';
@endphp

@section('content')
<section class="bg-brown py-10 text-white sm:py-14">
    <div class="container-site">
        <nav aria-label="Breadcrumb" class="mb-4 text-xs text-white/70">
            <a href="{{ route('home') }}" class="hover:text-accent">Home</a> / <a href="{{ route('packages.index') }}" class="hover:text-accent">Packages</a> / <span>{{ $package->title }}</span>
        </nav>
        <p class="label-caps text-accent">{{ $package->tourType?->name }} &middot; {{ $package->destination?->name }} &middot; {{ $package->nights }} nights</p>
        <h1 class="mt-3 text-4xl font-medium sm:text-5xl">{{ $package->title }}</h1>
        <p class="mt-3 max-w-2xl text-white/85">{{ $package->summary }}</p>
    </div>
</section>

<div class="container-site grid gap-10 py-10 lg:grid-cols-[1.4fr_1fr] lg:py-14">
    <div class="min-w-0 space-y-10">
        @if ($cover)
            <x-picture :media="$cover" :alt="$cover->alt" priority class="w-full rounded-2xl object-cover" sizes="(min-width:1024px) 60vw, 100vw" />
        @else
            <x-picture :src="$fallback" :alt="$package->tourType?->image_alt ?? ''" :width="960" :height="640" priority class="w-full rounded-2xl object-cover" />
        @endif

        @if ($package->description)<div class="prose-site">{!! clean_html($package->description) !!}</div>@endif

        @if ($package->highlights)
            <section aria-labelledby="hl"><h2 id="hl" class="text-2xl font-medium">Highlights</h2>
                <ul class="mt-3 grid gap-2 sm:grid-cols-2">@foreach ($package->highlights as $h)<li class="flex gap-2"><x-icon name="check" class="mt-0.5 h-5 w-5 shrink-0 text-rust" />{{ $h }}</li>@endforeach</ul>
            </section>
        @endif

        @if ($package->itinerary)
            <section aria-labelledby="it"><h2 id="it" class="text-2xl font-medium">Itinerary</h2>
                <div class="mt-3 space-y-3">@foreach ($package->itinerary as $day)<x-faq-item :question="$day['title']">{{ $day['body'] }}</x-faq-item>@endforeach</div>
            </section>
        @endif

        <div class="grid gap-6 sm:grid-cols-2">
            @if ($package->inclusions)<section aria-labelledby="inc"><h2 id="inc" class="text-xl font-medium">Included</h2><ul class="mt-3 space-y-1.5 text-sm">@foreach ($package->inclusions as $i)<li class="flex gap-2"><x-icon name="check" class="h-5 w-5 shrink-0 text-emerald-700" />{{ $i }}</li>@endforeach</ul></section>@endif
            @if ($package->exclusions)<section aria-labelledby="exc"><h2 id="exc" class="text-xl font-medium">Not included</h2><ul class="mt-3 space-y-1.5 text-sm">@foreach ($package->exclusions as $i)<li class="flex gap-2"><x-icon name="no" class="h-5 w-5 shrink-0 text-rust" />{{ $i }}</li>@endforeach</ul></section>@endif
        </div>

        @if ($package->images->count() > 1)
            <section aria-labelledby="gal"><h2 id="gal" class="text-2xl font-medium">Gallery</h2>
                <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3">@foreach ($package->images->skip(1) as $img)<x-picture :media="$img" :alt="$img->alt" class="aspect-[4/3] w-full rounded-xl object-cover" sizes="(min-width:640px) 20vw, 50vw" />@endforeach</div>
            </section>
        @endif

        @if ($package->faqs)
            <section aria-labelledby="pf"><h2 id="pf" class="text-2xl font-medium">Questions about this tour</h2>
                <div class="mt-3 space-y-3">@foreach ($package->faqs as $f)<x-faq-item :question="$f['question']">{{ $f['answer'] }}</x-faq-item>@endforeach</div>
            </section>
        @endif
    </div>

    <aside class="lg:sticky lg:top-24 lg:self-start" aria-label="Get a quote">
        <div id="quote" class="rounded-2xl border-4 border-accent bg-white p-5 shadow-card sm:p-6">
            <p class="text-sm">From <strong class="font-display text-3xl">{{ $package->priceLabel() }}</strong> <span class="text-ink/60">per person</span></p>
            <h2 class="mt-2 mb-4 text-xl font-medium">Get a free quote for this tour</h2>
            <x-enquiry-form form="package-quote" idp="pkg-quote" :package="$package" extra submit="Get My Free Quote" />
        </div>
        <details class="mt-4 rounded-2xl border border-sand-200 bg-white p-5">
            <summary class="cursor-pointer font-semibold">Ask us a question about this tour</summary>
            <div class="mt-4"><x-enquiry-form form="package-question" idp="pkg-question" :package="$package" submit="Ask Us a Question" /></div>
        </details>
    </aside>
</div>
@endsection
