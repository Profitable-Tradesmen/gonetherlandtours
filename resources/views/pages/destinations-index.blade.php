@extends('layouts.app')

@section('content')
<section class="bg-brown py-12 text-white sm:py-16"><div class="container-site">
    <nav aria-label="Breadcrumb" class="mb-4 text-xs text-white/70"><a href="{{ route('home') }}" class="hover:text-accent">Home</a> / <span>{{ text('destinations_heading') }}</span></nav>
    <h1 class="text-4xl font-medium sm:text-5xl">{{ text('destinations_heading') }}</h1>
    @if (text('destinations_intro'))<p class="mt-3 max-w-2xl text-white/90">{{ text('destinations_intro') }}</p>@endif
</div></section>

<section class="py-12 sm:py-16" aria-label="All destinations">
    <div class="container-site">
        @if ($destinations->isNotEmpty())
            <ul class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($destinations as $i => $d)
                    <li>
                        <a href="{{ route('destinations.show', $d) }}" class="group relative isolate flex min-h-[22rem] flex-col justify-end overflow-hidden rounded-2xl p-6 text-white shadow-card">
                            @if ($d->image)<x-picture :src="$d->image" :alt="$d->image_alt ?? ''" :width="960" :height="640" class="absolute inset-0 -z-20 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" sizes="(min-width:1024px) 33vw, (min-width:640px) 50vw, 100vw" />@endif
                            <span class="absolute inset-0 -z-10 bg-gradient-to-t from-ink/85 via-ink/30 to-transparent"></span>
                            <span class="label-caps text-accent">{{ $d->packages_count }} {{ \Illuminate\Support\Str::plural('tour', $d->packages_count) }}</span>
                            <h2 class="mt-1 text-3xl font-medium">{{ $d->name }}</h2>
                            @if ($d->summary)<p class="mt-2 line-clamp-3 text-sm text-white/85">{{ $d->summary }}</p>@endif
                            <span class="mt-4 inline-flex items-center gap-2 text-sm font-bold text-accent">Explore {{ $d->name }} <x-icon name="arrow" class="h-4 w-4 transition-transform group-hover:translate-x-1" /></span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-center">Destinations are coming soon.</p>
        @endif
        <p class="mt-10 text-center"><x-button @click="$store.modal.show()">Get a Free Quote</x-button></p>
    </div>
</section>
@endsection
