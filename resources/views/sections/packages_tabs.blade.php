@php
    $featured = \App\Models\Package::published()->where('is_featured', true)->with(['tourType', 'destination', 'images'])->orderBy('sort_order')->take(24)->get();
    $types = \App\Support\SiteData::tourTypes();
@endphp
@if ($featured->isNotEmpty() && ($d['layout'] ?? 'by_type') === 'by_type')
<section class="bg-sand py-16 sm:py-24" aria-labelledby="pk-{{ $sid }}">
    <div class="container-site">
        <x-section-heading data-reveal="up" :eyebrow="$d['eyebrow'] ?: null" :title="$d['heading']" :subtitle="$d['intro'] ?: null" :id="'pk-'.$sid" />
        {{-- Three columns on desktop, one per tour type that has a featured package: heading, summary, "view more" button, then its package cards stacked. One column on phones. --}}
        @php $columns = $types->filter(fn ($t) => $featured->where('tour_type_id', $t->id)->isNotEmpty())->values(); @endphp
        {{-- From lg the columns share 4 rows (title, summary, button, cards) via subgrid, so the cards line up even when a title wraps. --}}
        <div class="grid gap-x-6 gap-y-12 md:grid-cols-2 lg:grid-cols-3 lg:grid-rows-[auto_auto_auto_1fr] lg:gap-y-0">
            @foreach ($columns as $t)
                @php $colPackages = $featured->where('tour_type_id', $t->id)->take(3); @endphp
                <div class="flex flex-col lg:row-span-4 lg:grid lg:grid-rows-subgrid" data-reveal="up" style="--d:{{ $loop->index * 120 }}ms">
                    <h3 class="flex items-center gap-3 text-2xl font-medium sm:text-3xl"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-900 text-accent"><x-icon :name="$t->icon ?: 'check'" class="h-5 w-5" /></span>{{ $t->name }} Packages</h3>
                    <p class="mt-2 text-ink/75">{{ $t->summary }}</p>
                    <p class="mt-4">@if ($d['more_label'])<x-button :href="route('tour-types.show', $t)">{{ $d['more_label'] }} <x-icon name="arrow" class="h-4 w-4" /></x-button>@endif</p>
                    <div class="mt-6 flex flex-col gap-6">
                        @foreach ($colPackages as $p)
                            @include('partials.package-card', ['package' => $p])
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
        @if ($d['button_label'])<p class="mt-10 text-center"><x-button variant="secondary" :href="route('packages.index')">{{ $d['button_label'] }}</x-button></p>@endif
    </div>
</section>
@elseif ($featured->isNotEmpty())
<section class="bg-sand py-16 sm:py-24" aria-labelledby="pk-{{ $sid }}" x-data="{ tab: 'all' }">
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
            <button type="button" class="carousel-arrow absolute -left-3 top-1/3 hidden h-11 w-11 items-center justify-center rounded-full bg-accent text-ink shadow-card hover:bg-accent-600 disabled:cursor-default disabled:opacity-0 lg:flex" @click="step(-1)" :disabled="atStart" aria-label="Previous packages"><x-icon name="arrow" class="h-5 w-5 rotate-180" /></button>
            <button type="button" class="carousel-arrow absolute -right-3 top-1/3 hidden h-11 w-11 items-center justify-center rounded-full bg-accent text-ink shadow-card hover:bg-accent-600 disabled:cursor-default disabled:opacity-0 lg:flex" @click="step(1)" :disabled="atEnd" aria-label="Next packages"><x-icon name="arrow" class="h-5 w-5" /></button>
        </div>

        @if ($d['button_label'])<p class="mt-8 text-center"><x-button variant="secondary" :href="route('packages.index')">{{ $d['button_label'] }}</x-button></p>@endif
    </div>
</section>
@endif
