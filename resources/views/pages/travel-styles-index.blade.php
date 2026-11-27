@extends('layouts.app')

@section('content')
<x-page-banner image="images/off_the_beaten_path.webp" heading="Travel styles" :text="$intro">
    <x-slot:crumbs><nav aria-label="Breadcrumb" class="mb-4 text-xs text-white/80"><a href="{{ route('home') }}" class="hover:text-accent">Home</a> / <span>Travel styles</span></nav></x-slot:crumbs>
</x-page-banner>

<section class="py-12 sm:py-16" aria-label="All travel styles">
    <div class="container-site">
        @if ($styles->isNotEmpty())
            <ul class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($styles as $s)
                    <li>
                        <a href="{{ route('travel-styles.show', $s) }}" class="group relative isolate flex h-full min-h-[22rem] flex-col justify-end overflow-hidden rounded-2xl bg-brand-900 p-6 text-white shadow-card">
                            @if ($s->image)<x-picture :src="$s->image" :alt="$s->image_alt ?? ''" :width="960" :height="640" :lazy="! $loop->first" class="absolute inset-0 -z-20 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" sizes="(min-width:1024px) 33vw, (min-width:640px) 50vw, 100vw" />@endif
                            <span class="absolute inset-0 -z-10 bg-gradient-to-t from-ink/95 via-ink/60 to-ink/10"></span>
                            <h2 class="text-3xl font-medium">{{ $s->name }}</h2>
                            @if ($s->summary)<p class="mt-2 text-sm text-white/90">{{ $s->summary }}</p>@endif
                            <span class="mt-4 inline-flex items-center gap-2 text-sm font-bold text-accent">Explore<span class="sr-only"> {{ $s->name }}</span> <x-icon name="arrow" class="h-4 w-4 transition-transform group-hover:translate-x-1" /></span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-center">Travel styles are coming soon.</p>
        @endif
        <p class="mt-10 text-center"><x-button @click="$store.modal.show()">Get a free quote</x-button></p>
    </div>
</section>

<section class="bg-brand-900 py-12 text-white sm:py-16" aria-labelledby="ts-ways">
    <div class="container-site flex flex-col items-start gap-6 md:flex-row md:items-center md:justify-between">
        <div class="max-w-2xl">
            <h2 id="ts-ways" class="text-3xl font-medium">Next, choose <em class="text-accent">how you travel</em></h2>
            <p class="mt-3 text-white/90">Any style works as a self drive, with a private driver-guide or on a small-group coach tour.</p>
        </div>
        <x-button :href="route('ways-to-travel')" class="shrink-0">Compare ways to travel <x-icon name="arrow" class="h-4 w-4" /></x-button>
    </div>
</section>
@include('partials.how-it-works', ['bg' => 'sand'])
@endsection
