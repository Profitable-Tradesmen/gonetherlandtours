@extends('layouts.app')

@section('content')
<x-page-banner image="images/places/journal-getting-around.webp" alt="A country road lined with tall trees in the Netherlands" heading="Ways to travel" :text="$intro">
    <x-slot:crumbs><nav aria-label="Breadcrumb" class="mb-4 text-xs text-white/80"><a href="{{ route('home') }}" class="hover:text-accent">Home</a> / <span>Ways to travel</span></nav></x-slot:crumbs>
    <p class="mt-6"><x-button @click="$store.modal.show()">Get a free quote</x-button></p>
</x-page-banner>

<section class="bg-white py-14 sm:py-20" aria-labelledby="cmp-h">
    <div class="container-site">
        <x-section-heading eyebrow="Side by side" title="Compare the *three styles*" id="cmp-h" />
        <div class="overflow-x-auto rounded-2xl border border-ink/10 shadow-sm" tabindex="0" role="region" aria-label="Comparison table">
            <table class="w-full min-w-[40rem] text-left text-sm sm:text-base">
                <thead class="bg-brand-900 text-white">
                    <tr><td class="p-4"></td>@foreach ($types as $t)<th scope="col" class="p-4 font-display text-lg font-medium">{{ $t->name }}</th>@endforeach</tr>
                </thead>
                <tbody>
                    @foreach ($rows as $label => $values)
                        <tr class="border-t border-sand-200 align-top odd:bg-sand">
                            <th scope="row" class="p-4 font-bold text-brand-900">{{ $label }}</th>
                            @foreach ($types as $t)<td class="p-4">{{ $values[$t->slug] ?? '' }}</td>@endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>

@foreach ($types as $t)
    <section class="{{ $loop->odd ? 'bg-sand' : 'bg-white' }} py-14 sm:py-20" aria-labelledby="w-{{ $t->slug }}">
        <div class="container-site">
            <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div class="max-w-2xl">
                    <h2 id="w-{{ $t->slug }}" class="text-3xl font-medium sm:text-4xl">{{ $t->name }}</h2>
                    @if ($t->summary)<p class="mt-3 text-base leading-relaxed text-ink/80 sm:text-lg">{{ $t->summary }}</p>@endif
                </div>
                <x-button variant="secondary" :href="route('tour-types.show', $t)" class="shrink-0 self-start md:self-auto">About {{ $t->name }}</x-button>
            </div>
            @if ($t->packages->isNotEmpty())
                <x-slider class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($t->packages->sortBy('sort_order')->take(3) as $p)@include('partials.package-card-feature', ['package' => $p->setRelation('tourType', $t)])@endforeach
                </x-slider>
            @endif
        </div>
    </section>
@endforeach

@include('partials.how-it-works', ['bg' => 'white'])
@endsection
