@php
    // On a tour type page ($tourType) or a destination page ($destination) the controller passes it, and that tour type's / destination's packages are shown; anywhere else the featured ones.
    // Optional: skip the first N (offset), show at most N (limit), order by lowest price, and a "Get a free quote" button next to the "see all" button (used on the home page).
    $offset = max(0, (int) ($d['offset'] ?? 0));
    $limit = max(1, (int) ($d['limit'] ?? 12));
    $packages = \App\Models\Package::published()
        ->when(isset($tourType), fn ($q) => $q->where('tour_type_id', $tourType->id), fn ($q) => $q->when(isset($destination), fn ($q) => $q->where('destination_id', $destination->id), fn ($q) => $q->where('is_featured', true)))
        ->with(['tourType', 'destination', 'images'])
        ->when(($d['sort'] ?? 'default') === 'price', fn ($q) => $q->orderBy('price_from'))
        ->orderBy('sort_order')->skip($offset)->take($limit)->get();
    $bg = ($d['background'] ?? 'white') === 'sand' ? 'bg-sand' : 'bg-white';
@endphp
<section class="{{ $bg }} py-16 sm:py-24" aria-labelledby="tp-{{ $sid }}">
    <div class="container-site">
        <x-section-heading data-reveal="up" :eyebrow="$d['eyebrow'] ?: null" :title="$d['heading']" :subtitle="$d['intro'] ?: null" :id="'tp-'.$sid" />
        @if ($packages->isEmpty())
            <p class="text-center text-ink/75">{{ $d['empty_text'] }} <button type="button" class="font-bold text-brand-700 underline underline-offset-4 hover:text-rust" @click="$store.modal.show()">{{ text('card_quote_label') ?: 'Get a quote' }}</button></p>
        @else
            {{-- A plain grid of cards, the same on every package list: a swipe row on phones, then two columns from sm and three from lg. --}}
            <div class="carousel-track -mx-4 flex snap-x snap-mandatory gap-5 overflow-x-auto px-4 pb-4 sm:mx-0 sm:grid sm:grid-cols-2 sm:gap-6 sm:overflow-visible sm:px-0 sm:pb-0 lg:grid-cols-3">
                @foreach ($packages as $p)
                    <div class="flex w-[84%] shrink-0 snap-start sm:w-auto"><div class="w-full">@include('partials.package-card-feature', ['package' => $p])</div></div>
                @endforeach
            </div>
        @endif
        @if ($d['button_label'] || ! empty($d['quote_label']))
            <p class="mt-10 flex flex-wrap items-center justify-center gap-3">
                @if (! empty($d['quote_label']))<x-button @click="$store.modal.show()">{{ $d['quote_label'] }} <x-icon name="arrow" class="h-4 w-4" /></x-button>@endif
                @if ($d['button_label'])<x-button variant="secondary" :href="route('packages.index')">{{ $d['button_label'] }}</x-button>@endif
            </p>
        @endif
    </div>
</section>
