@extends('layouts.app')

@section('content')
<x-page-banner :image="$destination->image" :alt="$destination->image_alt ?? ''" :heading="e($destination->h1 ?: $destination->name)" :text="$destination->summary">
    <x-slot:crumbs><nav aria-label="Breadcrumb" class="mb-4 text-xs text-white/80"><a href="{{ route('home') }}" class="hover:text-accent">Home</a> / <a href="{{ route('destinations.index') }}" class="hover:text-accent">{{ text('destinations_heading') }}</a> / <span>{{ $destination->name }}</span></nav></x-slot:crumbs>
    <p class="mt-6"><x-button @click="$store.modal.show()">Get a free quote</x-button></p>
</x-page-banner>
@php $placesBlock = ['places' => \App\Models\Place::active()->where('destination_id', $destination->id)->get(), 'title' => 'Places in *'.$destination->name.'*', 'intro' => 'Pick the ones that interest you and we will build them into your route.', 'bg' => 'sand']; @endphp
@if (! empty($sections))
    {{-- The rest of the page is built from sections (Admin > Pages > "{{ $destination->name }} page"), the same blocks as the home and tour type pages. --}}
    @foreach ($sections as $section)
        @if ($loop->last && $section['type'] === 'cta_banner')@include('partials.places-section', $placesBlock)@endif
        @include('sections.'.$section['type'], ['d' => $section['d'], 'sid' => $section['id'], 'destination' => $destination])
    @endforeach
    @if (end($sections)['type'] !== 'cta_banner')@include('partials.places-section', $placesBlock)@endif
@else
<section class="container-site py-12">
    <div class="prose-site max-w-3xl">{!! clean_html($destination->body) !!}</div>
</section>
@if ($packages->isNotEmpty())
<section class="bg-sand py-14"><div class="container-site">
    <x-section-heading :title="'Packages in '.$destination->name" />
    <x-slider class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">@foreach ($packages as $p)@include('partials.package-card-feature', ['package' => $p])@endforeach</x-slider>
</div></section>
@endif
@include('partials.why-us')
@include('partials.places-section', $placesBlock)
@endif
@endsection
