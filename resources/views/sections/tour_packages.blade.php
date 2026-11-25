@php
    // On a tour type page ($tourType) or a destination page ($destination) the controller passes it, and that tour type's / destination's packages are shown; anywhere else the featured ones.
    $packages = \App\Models\Package::published()
        ->when(isset($tourType), fn ($q) => $q->where('tour_type_id', $tourType->id), fn ($q) => $q->when(isset($destination), fn ($q) => $q->where('destination_id', $destination->id), fn ($q) => $q->where('is_featured', true)))
        ->with(['tourType', 'destination', 'images'])->orderBy('sort_order')->take(12)->get();
@endphp
<section class="bg-white py-16 sm:py-24" aria-labelledby="tp-{{ $sid }}">
    <div class="container-site">
        <x-section-heading data-reveal="up" :eyebrow="$d['eyebrow'] ?: null" :title="$d['heading']" :subtitle="$d['intro'] ?: null" :id="'tp-'.$sid" />
        @if ($packages->isEmpty())
            <p class="text-center text-ink/75">{{ $d['empty_text'] }} <button type="button" class="font-bold text-brand-700 underline underline-offset-4 hover:text-rust" @click="$store.modal.show()">{{ text('card_quote_label') ?: 'Get a quote' }}</button></p>
        @else
            {{-- A plain grid of cards, the same on every package list: one column on phones, two from sm, three from lg. --}}
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($packages as $p)
                    @include('partials.package-card-feature', ['package' => $p])
                @endforeach
            </div>
        @endif
        @if ($d['button_label'])<p class="mt-8 text-center"><x-button variant="secondary" :href="route('packages.index')">{{ $d['button_label'] }}</x-button></p>@endif
    </div>
</section>
