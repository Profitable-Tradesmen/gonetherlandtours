@extends('layouts.app')

@php
    // Facts shown per tour type, worked out from its published packages.
    $info = $types->mapWithKeys(function ($t) {
        $list = $t->packages->sortBy('sort_order')->values();
        $cheapest = $list->filter(fn ($p) => $p->price_from)->sortBy('price_from')->first();
        $nights = $list->pluck('nights');

        return [$t->slug => [
            'list' => $list,
            'count' => $list->count(),
            'tours' => $list->count().' '.\Illuminate\Support\Str::plural('tour', $list->count()),
            'from' => $cheapest?->priceLabel(),
            'nights' => $nights->isEmpty() ? null : ($nights->min() === $nights->max() ? $nights->min() : $nights->min().'–'.$nights->max()).' nights',
        ]];
    });
    $icon = fn ($t) => $t->icon ?: 'check';
@endphp

@section('content')
<x-page-banner image="images/places/journal-getting-around.webp" alt="A country road lined with tall trees in the Netherlands" heading="Ways to travel" :text="$intro">
    <x-slot:crumbs><nav aria-label="Breadcrumb" class="mb-4 text-xs text-white/80"><a href="{{ route('home') }}" class="hover:text-accent">Home</a> / <span>Ways to travel</span></nav></x-slot:crumbs>
    <p class="mt-6"><x-button @click="$store.modal.show()">Get a free quote</x-button></p>
</x-page-banner>

{{-- 1. Pick a style: one photo card per tour type with the key facts and a jump to its tours further down. --}}
<section class="bg-sand py-14 sm:py-20" aria-labelledby="pick-h">
    <div class="container-site">
        <x-section-heading eyebrow="Choose your style" title="Three ways to see *the Netherlands*" subtitle="The same country, three different kinds of day. Start with the one that sounds like you." id="pick-h" />
        <ul class="grid gap-6 lg:grid-cols-3">
            @foreach ($types as $t)
                @php $i = $info[$t->slug]; @endphp
                <li class="flex">
                    <article class="group flex w-full flex-col overflow-hidden rounded-2xl border border-ink/10 bg-white shadow-card transition duration-300 hover:-translate-y-1">
                        <div class="relative isolate overflow-hidden">
                            @if ($t->image)<x-picture :src="$t->image" :alt="$t->image_alt ?? ''" :width="765" :height="1024" sizes="(min-width:1024px) 33vw, 100vw" class="h-64 w-full object-cover transition-transform duration-700 group-hover:scale-105 lg:h-72" />@else<div class="h-64 bg-brand-900 lg:h-72"></div>@endif
                            <span class="pointer-events-none absolute inset-0 bg-gradient-to-t from-ink/80 via-ink/15 to-transparent" aria-hidden="true"></span>
                            <div class="absolute inset-x-0 bottom-0 flex items-center gap-3 p-5 text-white">
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-accent text-ink"><x-icon :name="$icon($t)" class="h-6 w-6" /></span>
                                <h3 class="text-2xl font-medium sm:text-3xl">{{ $t->name }}</h3>
                            </div>
                        </div>
                        <div class="flex flex-1 flex-col p-5 sm:p-6">
                            @if ($t->summary)<p class="leading-relaxed text-ink/80">{{ $t->summary }}</p>@endif
                            @if (! empty($rows['Suits'][$t->slug]))
                                <p class="mt-4 border-l-[3px] border-accent pl-3 text-sm"><span class="label-caps block text-[0.65rem] font-bold text-rust">Best for</span><span class="font-semibold text-brand-900">{{ $rows['Suits'][$t->slug] }}</span></p>
                            @endif
                            <dl class="mt-5 grid grid-cols-3 divide-x divide-sand-200 border-y border-sand-200 text-center [&>div]:px-1 [&>div]:py-3">
                                <div><dt class="label-caps text-[0.6rem] text-ink/60">Tours</dt><dd class="mt-1 font-display text-xl text-brand-900">{{ $i['count'] }}</dd></div>
                                <div><dt class="label-caps text-[0.6rem] text-ink/60">Length</dt><dd class="mt-1 font-display text-xl text-brand-900">{{ $i['nights'] ? \Illuminate\Support\Str::before($i['nights'], ' ') : '–' }}<span class="ml-1 font-sans text-[0.65rem] font-semibold text-ink/60">nights</span></dd></div>
                                <div><dt class="label-caps text-[0.6rem] text-ink/60">From</dt><dd class="mt-1 font-display text-xl text-brand-900">{{ $i['from'] ?? 'Ask' }}</dd></div>
                            </dl>
                            <div class="mt-auto flex flex-wrap items-center justify-between gap-3 pt-5">
                                @if ($i['count'])<a href="#w-{{ $t->slug }}" class="btn btn-primary">See the tours<span class="sr-only">: {{ $t->name }}</span></a>@endif
                                <a href="{{ route('tour-types.show', $t) }}" class="inline-flex min-h-[2.75rem] items-center gap-1.5 text-sm font-bold text-brand-700 hover:text-rust"><span class="border-b border-current pb-0.5">About {{ $t->name }}</span><x-icon name="arrow" class="h-4 w-4" /></a>
                            </div>
                        </div>
                    </article>
                </li>
            @endforeach
        </ul>
    </div>
</section>

{{-- 2. Side by side: a table from md up; on phones one card per style, so nothing has to be scrolled sideways. --}}
<section class="bg-white py-14 sm:py-20" aria-labelledby="cmp-h">
    <div class="container-site">
        <x-section-heading eyebrow="Side by side" title="Compare the *three styles*" id="cmp-h" />
        <div class="hidden overflow-hidden rounded-2xl border border-ink/10 shadow-card md:block">
            <table class="w-full table-fixed text-left">
                <thead class="bg-brand-900 text-white">
                    <tr>
                        <td class="w-[22%] p-5"></td>
                        @foreach ($types as $t)
                            <th scope="col" class="border-l border-white/15 p-5 text-center align-bottom">
                                <span class="mx-auto flex h-11 w-11 items-center justify-center rounded-full bg-accent text-ink"><x-icon :name="$icon($t)" class="h-5 w-5" /></span>
                                <span class="mt-3 block font-display text-xl font-medium">{{ $t->name }}</span>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $label => $values)
                        <tr class="border-t border-sand-200 odd:bg-sand">
                            <th scope="row" class="p-5 align-middle text-sm font-bold text-brand-900">{{ $label }}</th>
                            @foreach ($types as $t)<td class="border-l border-sand-200 p-5 text-center align-middle">{{ $values[$t->slug] ?? '' }}</td>@endforeach
                        </tr>
                    @endforeach
                    <tr class="border-t border-sand-200">
                        <th scope="row" class="p-5 text-sm font-bold text-brand-900">Price per person</th>
                        @foreach ($types as $t)<td class="border-l border-sand-200 p-5 text-center"><span class="block font-display text-2xl text-brand-900">{{ $info[$t->slug]['from'] ? 'From '.$info[$t->slug]['from'] : 'On request' }}</span><a href="{{ route('tour-types.show', $t) }}" class="btn btn-secondary mt-3 !px-4">View<span class="sr-only"> {{ $t->name }}</span></a></td>@endforeach
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="space-y-5 md:hidden">
            @foreach ($types as $t)
                <div class="overflow-hidden rounded-2xl border border-ink/10 shadow-card">
                    <h3 class="flex items-center gap-3 bg-brand-900 p-4 text-xl font-medium text-white"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-accent text-ink"><x-icon :name="$icon($t)" class="h-5 w-5" /></span>{{ $t->name }}</h3>
                    <dl>
                        @foreach ($rows as $label => $values)
                            <div class="border-t border-sand-200 px-4 py-3 odd:bg-sand"><dt class="label-caps text-[0.65rem] font-bold text-rust">{{ $label }}</dt><dd class="mt-0.5">{{ $values[$t->slug] ?? '' }}</dd></div>
                        @endforeach
                    </dl>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- 3. The tours of each style. --}}
@foreach ($types as $t)
    @php $i = $info[$t->slug]; @endphp
    @continue(! $i['count'])
    <section id="w-{{ $t->slug }}" class="{{ $loop->odd ? 'bg-sand' : 'bg-white' }} scroll-mt-20 py-14 sm:py-20" aria-labelledby="t-{{ $t->slug }}">
        <div class="container-site">
            <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div class="min-w-0 max-w-2xl">
                    <p class="label-caps flex flex-wrap gap-x-3 gap-y-1 text-xs font-bold text-rust"><span>{{ $i['tours'] }}</span>@if ($i['nights'])<span aria-hidden="true">&middot;</span><span>{{ $i['nights'] }}</span>@endif @if ($i['from'])<span aria-hidden="true">&middot;</span><span>From {{ $i['from'] }}</span>@endif</p>
                    <h2 id="t-{{ $t->slug }}" class="mt-3 text-3xl font-medium sm:text-4xl">{{ trim(str_ireplace('tours', '', $t->name)) }} <em class="text-rust">tours</em></h2>
                    @if ($t->style_note)<p class="mt-3 flex items-start gap-2 text-ink/80"><x-icon :name="$icon($t)" class="mt-0.5 h-5 w-5 shrink-0 text-brand-700" />{{ $t->style_note }}</p>@endif
                </div>
                <x-button variant="secondary" :href="route('tour-types.show', $t)" class="shrink-0 self-start md:self-auto">{{ $i['count'] > 3 ? 'View all '.$i['count'].' tours' : 'About '.$t->name }} <x-icon name="arrow" class="h-4 w-4" /></x-button>
            </div>
            <x-slider class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($i['list']->take(3) as $p)@include('partials.package-card-feature', ['package' => $p->setRelation('tourType', $t)])@endforeach
            </x-slider>
        </div>
    </section>
@endforeach

@include('partials.how-it-works', ['bg' => $types->count() % 2 ? 'white' : 'sand'])

<section class="bg-brand-900 py-14 text-center text-white sm:py-20" aria-labelledby="help-h">
    <div class="container-site max-w-3xl">
        <h2 id="help-h" class="text-3xl font-medium sm:text-4xl">Not sure which way <em class="text-accent">suits you?</em></h2>
        <p class="mt-4 text-base leading-relaxed text-white/90 sm:text-lg">Tell us your dates, who is coming and how you like your days. A travel specialist will suggest a style, free and with no obligation.</p>
        <p class="mt-7 flex flex-wrap items-center justify-center gap-3">
            <x-button @click="$store.modal.show()">Get a free quote <x-icon name="arrow" class="h-4 w-4" /></x-button>
            <x-button variant="outline" class="text-white" :href="route('packages.index')">Browse all tours</x-button>
        </p>
    </div>
</section>
@endsection
