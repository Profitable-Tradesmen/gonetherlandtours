@extends('layouts.app')

@section('content')
<section class="bg-brown py-12 text-white sm:py-16">
    <div class="container-site">
        <nav aria-label="Breadcrumb" class="mb-4 text-xs text-white/70"><a href="{{ route('home') }}" class="hover:text-accent">Home</a> / <span>Packages</span></nav>
        <h1 class="text-4xl font-medium sm:text-5xl">{{ $h1 }}</h1>
        <p class="mt-3 max-w-2xl text-white/85">{{ text('packages_intro') }}</p>
    </div>
</section>

<section class="py-10 sm:py-14">
    <div class="container-site">
        <div class="mb-8 rounded-2xl bg-sand p-4" x-data="locationSearch('{{ route('packages.search') }}')">
            <form method="GET" action="{{ route('packages.index') }}" class="grid gap-3 sm:grid-cols-[1fr_1fr_auto]" aria-label="Filter packages">
                <div>
                    <label for="f-type" class="field-label">Tour type</label>
                    <select id="f-type" name="type" class="field"><option value="">All tour types</option>@foreach ($tourTypes as $t)<option value="{{ $t->slug }}" @selected(request('type') === $t->slug)>{{ $t->name }}</option>@endforeach</select>
                </div>
                <div>
                    <label for="f-dest" class="field-label">Destination</label>
                    <select id="f-dest" name="destination" class="field"><option value="">All destinations</option>@foreach ($destinations as $d)<option value="{{ $d->slug }}" @selected(request('destination') === $d->slug)>{{ $d->name }}</option>@endforeach</select>
                </div>
                <div class="flex items-end"><x-button type="submit" variant="secondary" block>Filter</x-button></div>
            </form>
            <p class="mt-3 text-sm">Looking for a specific place? <button type="button" class="font-bold text-brand-700 underline underline-offset-4 hover:text-rust" @click="show()">Search by location</button></p>

            {{-- Location search popup: results load via AJAX (no page reload) --}}
            <div x-show="open" x-cloak x-transition.opacity @keydown.escape.window="hide()" class="fixed inset-0 z-[70] flex items-end justify-center sm:items-center" role="dialog" aria-modal="true" aria-labelledby="loc-title">
                <div class="absolute inset-0 bg-ink/70" @click="hide()"></div>
                <div x-show="open" x-trap.noscroll="open" class="relative max-h-[92vh] w-full overflow-y-auto rounded-t-2xl bg-white p-5 shadow-2xl sm:max-w-xl sm:rounded-2xl sm:p-7">
                    <button type="button" @click="hide()" class="absolute right-3 top-3 rounded-full p-2 text-ink hover:bg-sand" aria-label="Close"><x-icon name="close" /></button>
                    <h2 id="loc-title" class="pr-10 text-2xl font-medium sm:text-3xl">Search by location</h2>
                    <form class="mt-4" @submit.prevent="run()" role="search">
                        <label for="loc-q" class="field-label">City, region or place</label>
                        <input id="loc-q" x-ref="input" type="search" x-model="q" @input.debounce.300ms="run()" maxlength="80" list="loc-list" placeholder="e.g. Amsterdam, Zeeland" autocomplete="off" class="field">
                        <datalist id="loc-list">@foreach ($destinations as $d)<option value="{{ $d->name }}">@endforeach</datalist>
                    </form>
                    <div class="mt-4" aria-live="polite" :class="loading && 'opacity-50'" x-html="html"></div>
                </div>
            </div>
        </div>

        @if ($packages->isEmpty())
            <p class="rounded-xl bg-sand p-6 text-center">No packages match those filters yet. <a href="{{ route('packages.index') }}" class="font-semibold text-rust underline">Clear filters</a> or <a href="{{ route('contact') }}" class="font-semibold text-rust underline">ask us to build one for you</a>.</p>
        @else
            <x-slider class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($packages as $p)@include('partials.package-card', ['package' => $p])@endforeach
            </x-slider>
        @endif
    </div>
</section>
@endsection
