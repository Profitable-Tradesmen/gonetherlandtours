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
                    <div data-slide class="flex w-[88%] shrink-0 snap-start"><div class="w-full">@include('partials.package-card-feature', ['package' => $p])</div></div>
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
                    <div x-ref="track" @scroll.passive="sync()" class="carousel-track relative -mx-4 flex snap-x snap-mandatory gap-5 overflow-x-auto scroll-smooth px-4 pb-4 sm:mx-0 sm:px-0 sm:grid sm:grid-cols-2 sm:gap-6 sm:overflow-visible sm:pb-0 lg:grid-cols-3">
                        @foreach ($rowPackages as $p)
                            <div data-slide class="flex w-[88%] shrink-0 snap-start sm:w-[calc(50%-0.625rem)] lg:w-[calc(33.333%-0.85rem)]"><div class="w-full">@include('partials.package-card-feature', ['package' => $p])</div></div>
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
@php
    $tabTypes = $types->filter(fn ($t) => $featured->where('tour_type_id', $t->id)->isNotEmpty())->values();
    $slugs = $featured->map(fn ($p) => (string) $p->tourType?->slug)->values();   // tour type of each card, in grid order
    $perPage = 6;
@endphp
{{-- Handpicked journeys (layout from the sister Go Austria site): heading on the left, filter pills and a "Showing N of M" count on the right, then a grid of cards. --}}
<section class="{{ ($d['background'] ?? 'sand') === 'white' ? 'bg-white' : 'bg-sand' }} py-16 sm:py-24" aria-labelledby="pk-{{ $sid }}" x-data="{
        tab: 'all', page: 1, per: {{ $perPage }}, types: @js($slugs),
        get total() { return this.tab === 'all' ? this.types.length : this.types.filter(t => t === this.tab).length },
        get pages() { return Math.max(1, Math.ceil(this.total / this.per)) },
        shows(i) { if (this.tab !== 'all' && this.types[i] !== this.tab) return false; const n = this.tab === 'all' ? i : this.types.slice(0, i).filter(t => t === this.tab).length; return Math.floor(n / this.per) === this.page - 1 },
        pick(t) { this.tab = t; this.page = 1; this.$refs.grid.scrollLeft = 0 },
        go(n) { this.page = Math.min(this.pages, Math.max(1, n)); this.$nextTick(() => { this.$refs.grid.scrollLeft = 0; if (this.$refs.grid.getBoundingClientRect().top < 0) this.$refs.grid.scrollIntoView({ block: 'start', behavior: 'smooth' }) }) }
    }">
    <div class="container-site">
        <div class="mb-10 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <x-section-heading data-reveal="up" class="!mb-0 max-w-2xl lg:min-w-0 lg:flex-1" :center="false" :eyebrow="$d['eyebrow'] ?: null" :title="$d['heading']" :subtitle="$d['intro'] ?: null" :id="'pk-'.$sid" />
            <div class="min-w-0 lg:shrink-0 lg:text-right">
                <div class="flex flex-wrap gap-2 lg:flex-nowrap lg:justify-end" role="tablist">
                    @php $pill = 'label-caps inline-flex min-h-[2.75rem] items-center whitespace-nowrap rounded-full border px-4 text-[0.65rem] font-semibold tracking-[0.12em] transition'; @endphp
                    <button type="button" role="tab" :aria-selected="tab === 'all'" class="{{ $pill }}" :class="tab === 'all' ? 'border-brand-900 bg-brand-900 text-white shadow-[0_3px_0_0_var(--color-accent)]' : 'border-ink/25 text-brand-900 hover:border-brand-900'" @click="pick('all')">{{ $d['all_label'] }}</button>
                    @foreach ($tabTypes as $t)
                        <button type="button" role="tab" :aria-selected="tab === '{{ $t->slug }}'" class="{{ $pill }}" :class="tab === '{{ $t->slug }}' ? 'border-brand-900 bg-brand-900 text-white shadow-[0_3px_0_0_var(--color-accent)]' : 'border-ink/25 text-brand-900 hover:border-brand-900'" @click="pick('{{ $t->slug }}')">{{ $t->name }}</button>
                    @endforeach
                </div>
                <p class="mt-4 text-xs tracking-wider text-ink/60" aria-live="polite">Showing <span x-text="(page - 1) * per + 1">1</span>–<span x-text="Math.min(page * per, total)">{{ min($perPage, $featured->count()) }}</span> of <span x-text="total">{{ $featured->count() }}</span> tours</p>
            </div>
        </div>

        {{-- Phones: swipeable row; from sm a plain grid (two, then three columns). --}}
        <div x-ref="grid" class="carousel-track scroll-mt-24 -mx-4 flex snap-x snap-mandatory gap-5 overflow-x-auto px-4 pb-4 sm:mx-0 sm:grid sm:grid-cols-2 sm:gap-6 sm:overflow-visible sm:px-0 sm:pb-0 lg:grid-cols-3">
            @foreach ($featured as $p)
                <div x-show="shows({{ $loop->index }})" @if ($loop->index >= $perPage) x-cloak @endif class="flex w-[84%] shrink-0 snap-start sm:w-auto">
                    <div class="w-full">@include('partials.package-card-feature', ['package' => $p])</div>
                </div>
            @endforeach
        </div>

        @php $pg = 'flex h-11 min-w-11 items-center justify-center rounded-lg border px-3 text-sm font-bold transition disabled:cursor-default disabled:opacity-40'; @endphp
        <nav class="mt-8 flex flex-wrap items-center justify-center gap-2" aria-label="Pages of tours" x-show="pages > 1" x-cloak>
            <button type="button" class="{{ $pg }} border-ink/20 bg-white text-brand-900 enabled:hover:border-brand-900" @click="go(page - 1)" :disabled="page === 1" aria-label="Previous page"><x-icon name="arrow" class="h-4 w-4 rotate-180" /></button>
            <template x-for="n in pages" :key="n">
                <button type="button" class="{{ $pg }}" :class="n === page ? 'border-brand-900 bg-brand-900 text-white' : 'border-ink/20 bg-white text-brand-900 hover:border-brand-900'" @click="go(n)" :aria-current="n === page ? 'page' : false" :aria-label="'Page ' + n" x-text="n"></button>
            </template>
            <button type="button" class="{{ $pg }} border-ink/20 bg-white text-brand-900 enabled:hover:border-brand-900" @click="go(page + 1)" :disabled="page === pages" aria-label="Next page"><x-icon name="arrow" class="h-4 w-4" /></button>
        </nav>

        <p class="mt-8 flex flex-wrap items-center justify-center gap-3"><x-button @click="$store.modal.show()">Get a free quote <x-icon name="arrow" class="h-4 w-4" /></x-button>@if ($d['button_label'])<x-button variant="secondary" :href="route('packages.index')">{{ $d['button_label'] }}</x-button>@endif</p>
    </div>
</section>
@endif
