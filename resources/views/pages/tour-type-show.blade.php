@extends('layouts.app')

@section('content')
@php
    // The card photo is tall (960x1430). The title bar is a wide banner, so use the landscape copy cut from it (name-hero.webp, plus a lighter
    // name-hero-800.webp for phones) when it exists: a phone then downloads about 90 KB instead of 200-340 KB, which is most of the page's LCP.
    $heroSrc = $tourType->image;
    $heroIsWide = false;
    if ($heroSrc && preg_match('/-(960|card)\.webp$/', $heroSrc) && is_file(public_path($try = preg_replace('/-(960|card)\.webp$/', '-hero.webp', $heroSrc)))) {
        $heroSrc = $try;
        $heroIsWide = true;
    }
@endphp
<x-page-banner :image="$heroSrc" :alt="$tourType->image_alt ?? ''" :heading="e($tourType->h1 ?: $tourType->name.' Netherlands Tours')" :text="$tourType->summary">
    <x-slot:crumbs><nav aria-label="Breadcrumb" class="mb-4 text-xs text-white/80"><a href="{{ route('home') }}" class="hover:text-accent">Home</a> / <a href="{{ route('packages.index') }}" class="hover:text-accent">Packages</a> / <span>{{ $tourType->name }}</span></nav></x-slot:crumbs>
    @if ($tourType->style_note)<p class="mt-3 flex items-center gap-2 text-sm font-semibold text-white"><x-icon name="guide" class="h-5 w-5 shrink-0 text-accent" />{{ $tourType->style_note }}</p>@endif
    <p class="mt-6"><x-button @click="$store.modal.show()">Get a free quote</x-button></p>
</x-page-banner>
@if (filled($tourType->body))
<section class="container-site py-12">
    <div class="prose-site max-w-3xl">{!! clean_html($tourType->body) !!}</div>
</section>
@endif
@php $placesBlock = ['places' => \App\Models\Place::forStops($packages->flatMap->routeStops()->all())->take(8), 'title' => 'Places on *these tours*', 'intro' => 'Some of the towns and sights our '.$tourType->name.' routes visit.', 'bg' => 'white']; @endphp
@if (! empty($sections))
    {{-- The rest of the page is built from sections (Admin > Pages > "{{ $tourType->name }} page"), the same blocks as the home page. --}}
    @foreach ($sections as $section)
        @if ($loop->last && $section['type'] === 'cta_banner')@include('partials.places-section', $placesBlock)@endif
        @include('sections.'.$section['type'], ['d' => $section['d'], 'sid' => $section['id'], 'tourType' => $tourType])
    @endforeach
    @if (end($sections)['type'] !== 'cta_banner')@include('partials.places-section', $placesBlock)@endif
@else
<section class="bg-sand py-14"><div class="container-site">
    <x-section-heading :title="$tourType->name.' packages'" />
    @if ($packages->isNotEmpty())
        <x-slider class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">@foreach ($packages as $p)@include('partials.package-card-feature', ['package' => $p])@endforeach</x-slider>
    @else
        <p class="mt-6 text-center">No {{ strtolower($tourType->name) }} packages are published yet. <button type="button" class="font-bold text-brand-700 underline underline-offset-4" @click="$store.modal.show()">Ask us for a tailored quote</button>.</p>
    @endif
    <p class="mt-8 text-center"><a href="{{ route('packages.index') }}" class="font-bold text-brand-700 underline underline-offset-4 hover:text-rust">See all Netherlands packages</a></p>
</div></section>
@include('partials.why-us')
@include('partials.places-section', $placesBlock)
@endif
@if (empty($sections) && $faqs->isNotEmpty())
<section class="py-14"><div class="container-site max-w-3xl">
    <x-section-heading eyebrow="FAQ" title="Questions we are asked" />
    <div class="space-y-3">@foreach ($faqs as $f)<x-faq-item :question="$f->question" name="faq-tour-type">{{ $f->answer }}</x-faq-item>@endforeach</div>
    <p class="mt-6 text-center"><a href="{{ route('faq') }}" class="font-bold text-brand-700 underline underline-offset-4 hover:text-rust">Read all FAQs</a></p>
</div></section>
@endif
@endsection
