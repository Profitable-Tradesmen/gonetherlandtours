@extends('layouts.app')

@section('content')
<x-page-banner :image="$style->image" :alt="$style->image_alt ?? ''" :heading="e($style->name)" :text="$style->summary">
    <x-slot:crumbs><nav aria-label="Breadcrumb" class="mb-4 text-xs text-white/80"><a href="{{ route('home') }}" class="hover:text-accent">Home</a> / <a href="{{ route('travel-styles.index') }}" class="hover:text-accent">Travel styles</a> / <span>{{ $style->name }}</span></nav></x-slot:crumbs>
    <p class="mt-6"><x-button @click="$store.modal.show()">Get a free quote</x-button></p>
</x-page-banner>

<section class="bg-white py-12 sm:py-20" aria-labelledby="ts-about">
    <div class="container-site grid gap-10 lg:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)] lg:gap-16">
        <div class="min-w-0">
            <h2 id="ts-about" class="text-3xl font-medium sm:text-4xl">What this trip <em class="text-rust">feels like</em></h2>
            <div class="prose-site mt-5">{!! clean_html($style->body) !!}</div>
            @if ($items = $style->list('highlights'))
                <h3 class="mt-8 text-2xl font-medium">Highlights</h3>
                <ul class="mt-4 grid gap-3 sm:grid-cols-2">
                    @foreach ($items as $item)
                        <li class="flex gap-3"><span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-accent text-ink"><x-icon name="check" class="h-3 w-3" /></span><span>{{ $item }}</span></li>
                    @endforeach
                </ul>
            @endif
        </div>
        <aside class="min-w-0 self-start rounded-2xl border-t-[3px] border-accent bg-sand p-6 sm:p-8" aria-label="At a glance">
            <p class="label-caps text-xs font-bold text-rust">At a glance</p>
            @if ($style->best_time)
                <p class="mt-5 text-sm font-bold text-brand-900">Best time to go</p>
                <p class="mt-1">{{ $style->best_time }}</p>
            @endif
            @if ($places = $style->list('places', ','))
                <p class="mt-5 text-sm font-bold text-brand-900">Places you may visit</p>
                <ul class="mt-2 flex flex-wrap gap-x-4 gap-y-2">
                    @foreach ($places as $place)<li class="inline-flex items-center gap-1.5 text-sm font-semibold"><i class="h-1.5 w-1.5 rotate-45 bg-accent" aria-hidden="true"></i>{{ $place }}</li>@endforeach
                </ul>
            @endif
            <p class="mt-6 border-t border-sand-200 pt-5 text-sm leading-relaxed text-ink/80">Every trip can be changed to suit your dates, pace and interests.</p>
            <x-button class="mt-4 w-full" @click="$store.modal.show()">Plan this trip <x-icon name="arrow" class="h-4 w-4" /></x-button>
        </aside>
    </div>
</section>

@if ($packages->isNotEmpty())
<section class="bg-sand py-12 sm:py-20" aria-labelledby="ts-tours">
    <div class="container-site">
        <x-section-heading :title="'Tours for *'.$style->name.'*'" id="ts-tours" />
        <x-slider class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">@foreach ($packages as $p)@include('partials.package-card-feature', ['package' => $p])@endforeach</x-slider>
    </div>
</section>
@endif

@include('partials.places-section', ['places' => \App\Models\Place::forStops($style->list('places', ',')), 'title' => 'Places for *'.$style->name.'*', 'intro' => 'Stops that suit this style. We can add or swap any of them.', 'bg' => 'white', 'id' => 'ts-places'])

@include('partials.how-it-works', ['bg' => 'sand'])

@if ($others->isNotEmpty())
<section class="bg-white py-12 sm:py-16" aria-labelledby="ts-more">
    <div class="container-site text-center">
        <h2 id="ts-more" class="text-2xl font-medium sm:text-3xl">More ways to see the Netherlands</h2>
        <ul class="mt-6 flex flex-wrap justify-center gap-3">
            @foreach ($others as $o)
                <li><a href="{{ route('travel-styles.show', $o) }}" class="inline-flex min-h-[2.75rem] items-center rounded-full border border-ink/20 px-5 text-sm font-semibold text-brand-900 transition hover:border-brand-900 hover:bg-sand">{{ $o->name }}</a></li>
            @endforeach
        </ul>
    </div>
</section>
@endif
@endsection
