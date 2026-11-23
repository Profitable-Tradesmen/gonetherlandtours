@php
    $featured = \App\Models\Package::published()->where('is_featured', true)->with(['tourType', 'destination', 'images'])->orderBy('sort_order')->take(24)->get();
    $types = \App\Support\SiteData::tourTypes();
@endphp
@if ($featured->isNotEmpty() && ($d['layout'] ?? 'by_type') === 'by_type')
<section class="{{ ($d['background'] ?? 'sand') === 'white' ? 'bg-white' : 'bg-sand' }} py-16 sm:py-24" aria-labelledby="pk-{{ $sid }}">
    <div class="container-site">
        <x-section-heading data-reveal="up" :eyebrow="$d['eyebrow'] ?: null" :title="$d['heading']" :subtitle="$d['intro'] ?: null" :id="'pk-'.$sid" />
        {{-- Phones: ONE swipeable carousel of all featured packages (horizontal cards, next one peeking, dash indicators). --}}
        <div class="md:hidden" x-data="carousel" role="region" aria-roledescription="carousel" aria-label="{{ $d['heading'] }}">
            <div x-ref="track" @scroll.passive="sync()" class="carousel-track relative -mx-4 flex snap-x snap-mandatory gap-4 overflow-x-auto px-4 pb-4">
                @foreach ($featured->take(12) as $p)
                    <div data-slide class="flex w-[88%] shrink-0 snap-start"><div class="w-full">@include('partials.package-card', ['package' => $p])</div></div>
                @endforeach
            </div>
            <div class="mt-1 flex justify-center gap-2" x-show="count > 1" x-cloak>
                <template x-for="n in count" :key="n">
                    <button type="button" class="h-1.5 w-8 rounded-sm transition-colors" :class="n - 1 === idx ? 'bg-brand-700' : 'bg-brand-700/25'" @click="go(n - 1)" :aria-label="@js(text('carousel_dash_label')) + ' ' + n" :aria-current="n - 1 === idx"></button>
                </template>
            </div>
        </div>
        <div class="hidden md:block">
        {{-- One carousel row per tour type that has a featured package: heading, its summary, a "view more" button, a swipeable row of cards and dash indicators. --}}
        @foreach ($types as $t)
            @php $rowPackages = $featured->where('tour_type_id', $t->id)->take(9); @endphp
            @continue($rowPackages->isEmpty())
            <div class="{{ $loop->first ? '' : 'mt-14' }}" x-data="carousel" role="region" aria-roledescription="carousel" aria-label="{{ $t->name }}">
                <div class="mb-5 flex flex-wrap items-end justify-between gap-4">
                    <div class="min-w-0 max-w-2xl">
                        <h3 class="flex items-center gap-3 text-2xl font-medium sm:text-3xl"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-900 text-accent"><x-icon :name="$t->icon ?: 'check'" class="h-5 w-5" /></span>{{ trim($t->name.' '.text('row_packages_suffix')) }}</h3>
                        @if ($t->summary)<p class="mt-2 text-ink/75">{{ $t->summary }}</p>@endif
                    </div>
                    @if ($d['more_label'])<x-button :href="route('tour-types.show', $t)" class="shrink-0">{{ $d['more_label'] }} <x-icon name="arrow" class="h-4 w-4" /></x-button>@endif
                </div>
                <div class="relative">
                    <div x-ref="track" @scroll.passive="sync()" class="carousel-track relative -mx-4 flex snap-x snap-mandatory gap-5 overflow-x-auto scroll-smooth px-4 pb-4 sm:mx-0 sm:px-0">
                        @foreach ($rowPackages as $p)
                            <div data-slide class="flex w-[88%] shrink-0 snap-start sm:w-[calc(50%-0.625rem)] lg:w-[calc(33.333%-0.85rem)]"><div class="w-full">@include('partials.package-card', ['package' => $p])</div></div>
                        @endforeach
                    </div>
                    <button type="button" class="carousel-arrow absolute -left-3 top-1/3 hidden h-11 w-11 items-center justify-center rounded-full bg-accent text-ink shadow-card hover:bg-accent-600 disabled:cursor-default disabled:opacity-0 lg:flex" @click="step(-1)" :disabled="atStart" aria-label="{{ text('carousel_prev_label') }}"><x-icon name="arrow" class="h-5 w-5 rotate-180" /></button>
                    <button type="button" class="carousel-arrow absolute -right-3 top-1/3 hidden h-11 w-11 items-center justify-center rounded-full bg-accent text-ink shadow-card hover:bg-accent-600 disabled:cursor-default disabled:opacity-0 lg:flex" @click="step(1)" :disabled="atEnd" aria-label="{{ text('carousel_next_label') }}"><x-icon name="arrow" class="h-5 w-5" /></button>
                </div>
                {{-- dash indicators: one per card, the visible one is filled; tap to jump --}}
                <div class="mt-2 flex justify-center gap-2" x-show="count > 1" x-cloak>
                    <template x-for="n in count" :key="n">
                        <button type="button" class="h-1.5 w-10 rounded-sm transition-colors sm:w-14" :class="n - 1 === idx ? 'bg-brand-700' : 'bg-brand-700/25'" @click="go(n - 1)" :aria-label="@js(text('carousel_dash_label')) + ' ' + n" :aria-current="n - 1 === idx"></button>
                    </template>
                </div>
            </div>
        @endforeach
        </div>
        @if ($d['button_label'])<p class="mt-10 text-center"><x-button variant="secondary" :href="route('packages.index')">{{ $d['button_label'] }}</x-button></p>@endif
    </div>
</section>
@elseif ($featured->isNotEmpty())
<section class="{{ ($d['background'] ?? 'sand') === 'white' ? 'bg-white' : 'bg-sand' }} py-16 sm:py-24" aria-labelledby="pk-{{ $sid }}" x-data="{ tab: 'all' }">
    <div class="container-site">
        <x-section-heading data-reveal="up" :eyebrow="$d['eyebrow'] ?: null" :title="$d['heading']" :id="'pk-'.$sid" />
        <div class="mb-8 flex flex-wrap justify-center gap-2" role="tablist">
            <button type="button" role="tab" :aria-selected="tab === 'all'" class="btn !px-4 !py-2 !text-[0.72rem] sm:!px-6 sm:!text-[0.8rem]" :class="tab === 'all' ? 'btn-secondary' : 'btn-outline text-brand-700'" @click="tab = 'all'"><x-icon name="map" class="h-4 w-4" />{{ $d['all_label'] }}</button>
            @foreach ($types as $t)
                <button type="button" role="tab" :aria-selected="tab === '{{ $t->slug }}'" class="btn !px-4 !py-2 !text-[0.72rem] sm:!px-6 sm:!text-[0.8rem]" :class="tab === '{{ $t->slug }}' ? 'btn-secondary' : 'btn-outline text-brand-700'" @click="tab = '{{ $t->slug }}'"><x-icon :name="$t->icon ?: 'check'" class="h-4 w-4" />{{ $t->name }}</button>
            @endforeach
        </div>

        {{-- Swipeable carousel: native scroll-snap, arrows on larger screens (see Alpine "carousel" in app.js). --}}
        <div class="relative" x-data="carousel" x-init="$watch('tab', () => $nextTick(() => reset()))" role="region" aria-roledescription="carousel" aria-label="{{ $d['heading'] }}">
            <div x-ref="track" @scroll.passive="sync()" class="carousel-track relative -mx-4 flex snap-x snap-mandatory gap-5 overflow-x-auto scroll-smooth px-4 pb-4 sm:mx-0 sm:px-0">
                @foreach ($featured as $p)
                    <div data-slide x-show="tab === 'all' || tab === '{{ $p->tourType?->slug }}'" class="flex w-[84%] shrink-0 snap-start sm:w-[calc(50%-0.625rem)] lg:w-[calc(33.333%-0.85rem)]">
                        <div class="w-full">@include('partials.package-card', ['package' => $p])</div>
                    </div>
                @endforeach
            </div>
            <button type="button" class="carousel-arrow absolute -left-3 top-1/3 hidden h-11 w-11 items-center justify-center rounded-full bg-accent text-ink shadow-card hover:bg-accent-600 disabled:cursor-default disabled:opacity-0 lg:flex" @click="step(-1)" :disabled="atStart" aria-label="{{ text('carousel_prev_label') }}"><x-icon name="arrow" class="h-5 w-5 rotate-180" /></button>
            <button type="button" class="carousel-arrow absolute -right-3 top-1/3 hidden h-11 w-11 items-center justify-center rounded-full bg-accent text-ink shadow-card hover:bg-accent-600 disabled:cursor-default disabled:opacity-0 lg:flex" @click="step(1)" :disabled="atEnd" aria-label="{{ text('carousel_next_label') }}"><x-icon name="arrow" class="h-5 w-5" /></button>
        </div>

        @if ($d['button_label'])<p class="mt-8 text-center"><x-button variant="secondary" :href="route('packages.index')">{{ $d['button_label'] }}</x-button></p>@endif
    </div>
</section>
@endif
