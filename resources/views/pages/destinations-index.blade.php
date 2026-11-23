@extends('layouts.app')

@section('content')
<x-page-banner image="images/off_the_beaten_path.webp" :heading="e(text('destinations_heading'))" :text="text('destinations_intro') ?: null">
    <x-slot:crumbs><nav aria-label="Breadcrumb" class="mb-4 text-xs text-white/80"><a href="{{ route('home') }}" class="hover:text-accent">Home</a> / <span>{{ text('destinations_heading') }}</span></nav></x-slot:crumbs>
</x-page-banner>

<section class="py-12 sm:py-16" aria-label="All destinations">
    <div class="container-site">
        @if ($destinations->isNotEmpty())
            <x-slider tag="ul" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($destinations as $i => $d)
                    <li>
                        <a href="{{ route('destinations.show', $d) }}" class="group relative isolate flex min-h-[22rem] flex-col justify-end overflow-hidden rounded-2xl p-6 text-white shadow-card">
                            @if ($d->image)<x-picture :src="$d->image" :alt="$d->image_alt ?? ''" :width="960" :height="640" :lazy="! $loop->first" class="absolute inset-0 -z-20 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" sizes="(min-width:1024px) 33vw, (min-width:640px) 50vw, 100vw" />@endif
                            <span class="absolute inset-0 -z-10 bg-gradient-to-t from-ink/95 via-ink/60 to-ink/10"></span>
                            <span class="label-caps self-start rounded bg-ink/70 px-2 py-0.5 text-accent">{{ $d->packages_count }} {{ \Illuminate\Support\Str::plural('tour', $d->packages_count) }}</span>
                            <h2 class="mt-1 text-3xl font-medium">{{ $d->name }}</h2>
                            @if ($d->summary)<p class="mt-2 line-clamp-3 text-sm text-white/85">{{ $d->summary }}</p>@endif
                            <span class="mt-4 inline-flex items-center gap-2 text-sm font-bold text-accent">Explore {{ $d->name }} <x-icon name="arrow" class="h-4 w-4 transition-transform group-hover:translate-x-1" /></span>
                        </a>
                    </li>
                @endforeach
            </x-slider>
        @else
            <p class="text-center">Destinations are coming soon.</p>
        @endif
        <p class="mt-10 text-center"><x-button @click="$store.modal.show()">Get a Free Quote</x-button></p>
    </div>
</section>
@endsection
