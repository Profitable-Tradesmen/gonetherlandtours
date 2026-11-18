@php
    // On a tour type page ($tourType is passed by the controller) show that tour type's packages; anywhere else show the featured ones.
    $packages = \App\Models\Package::published()
        ->when(isset($tourType), fn ($q) => $q->where('tour_type_id', $tourType->id), fn ($q) => $q->where('is_featured', true))
        ->with(['tourType', 'destination', 'images'])->orderBy('sort_order')->take(12)->get();
@endphp
<section class="bg-white py-16 sm:py-24" aria-labelledby="tp-{{ $sid }}">
    <div class="container-site">
        <x-section-heading data-reveal="up" :eyebrow="$d['eyebrow'] ?: null" :title="$d['heading']" :subtitle="$d['intro'] ?: null" :id="'tp-'.$sid" />
        @if ($packages->isEmpty())
            <p class="text-center text-ink/75">{{ $d['empty_text'] }} <button type="button" class="font-bold text-brand-700 underline underline-offset-4 hover:text-rust" @click="$store.modal.show()">{{ text('card_quote_label') ?: 'Get a Quote' }}</button></p>
        @else
            {{-- Swipeable carousel (arrows on large screens, dash indicators below). Phones get the horizontal navy card. --}}
            <div x-data="carousel" role="region" aria-roledescription="carousel" aria-label="{{ $d['heading'] }}">
                <div class="relative">
                    <div x-ref="track" @scroll.passive="sync()" class="carousel-track relative -mx-4 flex snap-x snap-mandatory gap-5 overflow-x-auto px-4 pb-4 sm:mx-0 sm:px-0">
                        @foreach ($packages as $p)
                            <div data-slide class="flex w-[88%] shrink-0 snap-start sm:w-[calc(50%-0.625rem)] lg:w-[calc(33.333%-0.85rem)]"><div class="w-full">@include('partials.package-card', ['package' => $p])</div></div>
                        @endforeach
                    </div>
                    <button type="button" class="carousel-arrow absolute -left-3 top-1/3 hidden h-11 w-11 items-center justify-center rounded-full bg-accent text-ink shadow-card hover:bg-accent-600 disabled:cursor-default disabled:opacity-0 lg:flex" @click="step(-1)" :disabled="atStart" aria-label="{{ text('carousel_prev_label') }}"><x-icon name="arrow" class="h-5 w-5 rotate-180" /></button>
                    <button type="button" class="carousel-arrow absolute -right-3 top-1/3 hidden h-11 w-11 items-center justify-center rounded-full bg-accent text-ink shadow-card hover:bg-accent-600 disabled:cursor-default disabled:opacity-0 lg:flex" @click="step(1)" :disabled="atEnd" aria-label="{{ text('carousel_next_label') }}"><x-icon name="arrow" class="h-5 w-5" /></button>
                </div>
                <div class="mt-2 flex justify-center gap-2" x-show="count > 1" x-cloak>
                    <template x-for="n in count" :key="n">
                        <button type="button" class="h-1.5 w-10 rounded-sm transition-colors sm:w-14" :class="n - 1 === idx ? 'bg-brand-700' : 'bg-brand-700/25'" @click="go(n - 1)" :aria-label="@js(text('carousel_dash_label')) + ' ' + n" :aria-current="n - 1 === idx"></button>
                    </template>
                </div>
            </div>
        @endif
        @if ($d['button_label'])<p class="mt-8 text-center"><x-button variant="secondary" :href="route('packages.index')">{{ $d['button_label'] }}</x-button></p>@endif
    </div>
</section>
