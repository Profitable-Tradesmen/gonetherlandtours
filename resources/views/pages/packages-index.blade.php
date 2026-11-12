@extends('layouts.app')

@section('content')
<section class="bg-brown py-12 text-white sm:py-16">
    <div class="container-site">
        <nav aria-label="Breadcrumb" class="mb-4 text-xs text-white/70"><a href="{{ route('home') }}" class="hover:text-accent">Home</a> / <span>Packages</span></nav>
        <h1 class="text-4xl font-medium sm:text-5xl">{{ $h1 }}</h1>
        <p class="mt-3 max-w-2xl text-white/85">Browse our Netherlands tour packages and request a free, tailored quote.</p>
    </div>
</section>

<section class="py-10 sm:py-14">
    <div class="container-site">
        <form method="GET" action="{{ route('packages.index') }}" class="mb-8 grid gap-3 rounded-2xl bg-sand p-4 sm:grid-cols-[1fr_1fr_auto]" aria-label="Filter packages">
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

        @if ($packages->isEmpty())
            <p class="rounded-xl bg-sand p-6 text-center">No packages match those filters yet. <a href="{{ route('packages.index') }}" class="font-semibold text-rust underline">Clear filters</a> or <a href="{{ route('contact') }}" class="font-semibold text-rust underline">ask us to build one for you</a>.</p>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($packages as $p)@include('partials.package-card', ['package' => $p])@endforeach
            </div>
        @endif
    </div>
</section>
@endsection
