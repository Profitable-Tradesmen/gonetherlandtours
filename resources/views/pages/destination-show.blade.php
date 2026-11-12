@extends('layouts.app')

@section('content')
<section class="relative isolate overflow-hidden bg-brown py-14 text-white sm:py-20">
    @if ($destination->image)<x-picture :src="$destination->image" :alt="$destination->image_alt ?? ''" :width="960" :height="640" priority class="absolute inset-0 -z-20 h-full w-full object-cover" />@endif
    <div class="absolute inset-0 -z-10 bg-ink/65"></div>
    <div class="container-site">
        <nav aria-label="Breadcrumb" class="mb-4 text-xs text-white/70"><a href="{{ route('home') }}" class="hover:text-accent">Home</a> / <span>{{ $destination->name }}</span></nav>
        <h1 class="text-4xl font-medium sm:text-5xl">{{ $destination->h1 ?: $destination->name }}</h1>
        <p class="mt-3 max-w-2xl text-white/90">{{ $destination->summary }}</p>
        <x-button class="mt-6" @click="$store.modal.show()">Get a Free Quote</x-button>
    </div>
</section>
<section class="container-site py-12">
    <div class="prose-site max-w-3xl">{!! clean_html($destination->body) !!}</div>
</section>
@if ($packages->isNotEmpty())
<section class="bg-sand py-14"><div class="container-site">
    <x-section-heading :title="'Packages in '.$destination->name" />
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">@foreach ($packages as $p)@include('partials.package-card', ['package' => $p])@endforeach</div>
</div></section>
@endif
@endsection
