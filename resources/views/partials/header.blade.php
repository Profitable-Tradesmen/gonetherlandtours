{{-- Sticky header with full-width mega menus. Items come from Admin > Navigation (menu "header"): a "Tours" item shows the
     auto mega menu of tour types, an item with children shows a dropdown of image cards, other items are plain links.
     $tourTypes (with published packages) and $destinations come from the layout. Labels come from Admin > Settings. --}}
@php
    $navBtn = 'hdr-link inline-flex items-center gap-1.5 whitespace-nowrap rounded-lg px-3 py-2.5 text-[0.9rem] font-medium xl:px-4 text-ink transition-colors hover:bg-sand';
    $menu = \App\Models\MenuItem::tree('header');
    $hasTours = $menu->contains('kind', 'tours');
    $overlay = ! empty($overlayHeader);   // the page starts with a photo hero: the header is fully transparent over it (white links, white logo) until you scroll, then it becomes a white bar
@endphp
<header class="hdr sticky top-0 z-50 bg-white/95 shadow-[0_6px_20px_-6px_rgb(12_13_17/0.28)] backdrop-blur {{ $overlay ? 'hdr-overlay -mb-[4.25rem] lg:-mb-[5.25rem]' : '' }}"
        x-data="{ menu: false, dd: null, solid: false }" x-init="solid = window.scrollY > 24"
        @scroll.window.passive="solid = window.scrollY > 24"
        :class="{ 'is-solid': solid || menu || dd }"
        @keydown.escape.window="menu = false; dd = null" @mouseleave="dd = null">
    <div class="container-site flex h-16 items-center justify-between gap-4 lg:h-20">
        <a href="{{ route('home') }}" @mouseenter="dd = null"><span class="hdr-logo inline-block">@include('partials.logo')</span></a>

        <nav class="hidden items-center gap-1 lg:ml-auto lg:flex" aria-label="Main">
            @foreach ($menu as $item)
                @if ($item->kind === 'tours')
                    <button type="button" class="{{ $navBtn }}" :class="dd === 'tours' && 'bg-sand'" @click="dd = dd === 'tours' ? null : 'tours'" @mouseenter="dd = 'tours'" :aria-expanded="dd === 'tours'" aria-controls="mega-tours">
                        {{ $item->label }} <x-icon name="chevron" class="h-4 w-4 transition-transform" ::class="dd === 'tours' && 'rotate-180'" />
                    </button>
                @elseif ($item->kind === 'cards' && $item->children->isNotEmpty())
                    <button type="button" class="{{ $navBtn }}" :class="dd === 'm{{ $item->id }}' && 'bg-sand'" @click="dd = dd === 'm{{ $item->id }}' ? null : 'm{{ $item->id }}'" @mouseenter="dd = 'm{{ $item->id }}'" :aria-expanded="dd === 'm{{ $item->id }}'" aria-controls="mega-{{ $item->id }}">
                        {{ $item->label }} <x-icon name="chevron" class="h-4 w-4 transition-transform" ::class="dd === 'm{{ $item->id }}' && 'rotate-180'" />
                    </button>
                @elseif ($item->kind === 'link' && $destinations->isNotEmpty() && rtrim($item->href(), '/') === rtrim(route('destinations.index'), '/'))
                    <button type="button" class="{{ $navBtn }}" :class="dd === 'dest' && 'bg-sand'" @click="dd = dd === 'dest' ? null : 'dest'" @mouseenter="dd = 'dest'" :aria-expanded="dd === 'dest'" aria-controls="mega-destinations">
                        {{ $item->label }} <x-icon name="chevron" class="h-4 w-4 transition-transform" ::class="dd === 'dest' && 'rotate-180'" />
                    </button>
                @else
                    <a href="{{ $item->href() }}" class="{{ $navBtn }}" @if ($item->new_tab) target="_blank" rel="noopener" @endif @mouseenter="dd = null">{{ $item->label }}</a>
                @endif
            @endforeach
        </nav>

        <div class="flex items-center gap-2">
            @if (text('header_button_label'))<x-button class="hidden sm:inline-flex lg:hidden !py-2.5" @click="$store.modal.show()">{{ text('header_button_label') }}</x-button>@endif
            <button type="button" class="hdr-link rounded p-2 text-ink hover:bg-sand lg:hidden" @click="menu = !menu" :aria-expanded="menu" aria-controls="mobile-menu" aria-label="Menu">
                <x-icon name="menu" x-show="!menu" /><x-icon name="close" x-show="menu" x-cloak />
            </button>
        </div>
    </div>

    {{-- Mega menus: full-width dark panels in the style of gorailtravel.com (navy panel, section headings with a round icon, rows with a thumbnail, a featured card,
         photo cards and a bottom bar with a short tagline and buttons). All content is automatic or comes from Admin > Navigation, Tour types, Destinations and Settings. --}}
    @php
        $megaPanel = 'absolute inset-x-0 top-full hidden bg-brand-900 text-white shadow-[0_18px_28px_-12px_rgb(12_13_17/0.5)] lg:block';
        $megaIn = 'transition ease-out duration-200';
        $megaBar = [
            'tagline' => text('mega_tagline') ?: text('mega_help_text'),
            'packages' => text('mega_all_packages_label'),
            'quote' => text('mega_quote_label'),
        ];
    @endphp

    {{-- Tour types: ways to travel (left), featured trips (middle) and one featured trip card (right). --}}
    @if ($hasTours)
        @php
            $trips = $tourTypes->flatMap(fn ($t) => $t->packages->each(fn ($p) => $p->setRelation('tourType', $t)))->unique('id');   // tour type is already loaded: no extra query per package
            $featured = $trips->firstWhere('is_featured', true) ?? $trips->first();
            $tripList = $trips->take(5);
        @endphp
        <div id="mega-tours" x-show="dd === 'tours'" x-cloak x-transition:enter="{{ $megaIn }}" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="{{ $megaPanel }}" @click.outside="dd === 'tours' && (dd = null)">
            <div class="container-site grid gap-x-10 gap-y-6 pt-8 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,1fr)_minmax(0,0.9fr)]">
                {{-- ways to travel --}}
                <div class="min-w-0">
                    <div class="flex items-center gap-3"><span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-white/10 text-accent"><x-icon name="map" class="h-5 w-5" /></span>
                        <div><p class="font-display text-xl font-medium leading-tight">{{ text('mega_tours_heading') }}</p><p class="text-sm text-white/70">{{ text('mega_tours_sub') }}</p></div></div>
                    <ul class="mt-4">
                        @foreach ($tourTypes as $t)
                            <li class="border-t border-white/15 first:border-0">
                                <a href="{{ route('tour-types.show', $t) }}" class="group flex items-center gap-4 py-3" @click="dd = null">
                                    <span class="block h-14 w-20 shrink-0 overflow-hidden rounded-lg bg-white/10"><x-picture :src="$t->image ?: 'images/tour-self-drive.webp'" :alt="$t->image_alt ?? ''" :width="160" :height="112" sizes="80px" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" /></span>
                                    <span class="min-w-0 flex-1"><span class="block font-display text-lg font-medium leading-tight transition-colors group-hover:text-accent">{{ $t->name }}</span><span class="block truncate text-sm text-white/70">{{ $t->style_note ?: $t->summary }}</span></span>
                                    <x-icon name="arrow" class="h-4 w-4 shrink-0 text-accent transition-transform group-hover:translate-x-1" />
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('packages.index') }}" class="label-caps mt-3 inline-flex items-center gap-2 text-xs text-accent hover:text-white" @click="dd = null">{{ text('mega_view_all_label') }} packages <x-icon name="arrow" class="h-3.5 w-3.5" /></a>
                </div>
                {{-- featured trips --}}
                <div class="min-w-0">
                    <div class="flex items-center gap-3"><span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-white/10 text-accent"><x-icon name="star" class="h-5 w-5" /></span>
                        <div><p class="font-display text-xl font-medium leading-tight">{{ text('mega_featured_label') ?: 'Featured trips' }}</p><p class="text-sm text-white/70">{{ text('mega_trips_sub') }}</p></div></div>
                    <ul class="mt-4">
                        @forelse ($tripList as $p)
                            @php $cover = $p->relationLoaded('images') ? $p->images->first() : null; @endphp
                            <li class="border-t border-white/15 first:border-0">
                                <a href="{{ route('packages.show', $p) }}" class="group flex items-center gap-3 py-2.5" @click="dd = null">
                                    <span class="block h-11 w-11 shrink-0 overflow-hidden rounded-lg bg-white/10">@if ($cover)<x-picture :media="$cover" :alt="$cover->alt" sizes="44px" class="h-full w-full object-cover" />@else<x-picture :src="$p->tourType?->image ?: 'images/tour-self-drive.webp'" alt="" :width="120" :height="120" sizes="44px" class="h-full w-full object-cover" />@endif</span>
                                    <span class="min-w-0 flex-1"><span class="block truncate font-semibold leading-tight transition-colors group-hover:text-accent">{{ $p->title }}</span><span class="block truncate text-xs text-white/70">{{ $p->nights }} {{ \Illuminate\Support\Str::plural('night', $p->nights) }}@if ($p->destination) · {{ $p->destination->name }}@endif</span></span>
                                    <x-icon name="arrow" class="h-4 w-4 shrink-0 text-accent opacity-0 transition group-hover:opacity-100" />
                                </a>
                            </li>
                        @empty
                            <li class="py-3 text-sm text-white/70">{{ text('mega_help_text') }}</li>
                        @endforelse
                    </ul>
                </div>
                {{-- one featured trip card --}}
                @if ($featured)
                    @php $fc = $featured->relationLoaded('images') ? $featured->images->first() : null; @endphp
                    <a href="{{ route('packages.show', $featured) }}" class="group block min-w-0 self-start overflow-hidden rounded-xl bg-white/10 ring-1 ring-white/15" @click="dd = null">
                        <span class="relative block overflow-hidden">
                            @if ($fc)<x-picture :media="$fc" :alt="$fc->alt" sizes="320px" class="aspect-[16/10] w-full object-cover transition-transform duration-700 group-hover:scale-105" />@else<x-picture :src="$featured->tourType?->image ?: 'images/tour-self-drive.webp'" alt="" :width="640" :height="400" sizes="320px" class="aspect-[16/10] w-full object-cover transition-transform duration-700 group-hover:scale-105" />@endif
                            @if (text('mega_badge_label'))<span class="label-caps absolute right-0 top-3 bg-accent px-3 py-1.5 text-[0.65rem] text-ink">{{ text('mega_badge_label') }}</span>@endif
                        </span>
                        <span class="block p-4">
                            <span class="label-caps block text-[0.65rem] text-accent">{{ $featured->nights }} {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::plural('night', $featured->nights)) }}@if ($featured->destination) · {{ $featured->destination->name }}@endif</span>
                            <span class="mt-1 block font-display text-xl font-medium leading-snug">{{ $featured->title }}</span>
                            <span class="mt-3 block border-t border-white/20 pt-3 text-sm text-white/80">@if ($featured->price_from){{ text('card_from_label') ?: 'From' }} <span class="font-display text-xl font-medium text-accent">{{ $featured->priceLabel() }}</span>@if (text('card_per_person')) <span class="text-xs">{{ text('card_per_person') }}</span>@endif @else{{ $megaBar['quote'] ?: 'Get a quote' }}@endif</span>
                        </span>
                    </a>
                @endif
            </div>
            @include('partials.mega-bar', ['bar' => $megaBar])
        </div>
    @endif

    {{-- Destinations: a row of photo cards (shown when the "Destinations" link goes to /destinations). --}}
    @if ($destinations->isNotEmpty())
        <div id="mega-destinations" x-show="dd === 'dest'" x-cloak x-transition:enter="{{ $megaIn }}" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="{{ $megaPanel }}" @click.outside="dd === 'dest' && (dd = null)">
            @include('partials.mega-cards', ['cards' => $destinations->take(6)->map(fn ($d) => ['href' => route('destinations.show', $d), 'image' => $d->image, 'alt' => $d->image_alt ?? '', 'title' => $d->name, 'text' => $d->summary, 'new_tab' => false]), 'link' => text('mega_discover_label')])
            @include('partials.mega-bar', ['bar' => ['tagline' => text('mega_destinations_tagline') ?: $megaBar['tagline'], 'packages' => '', 'quote' => $megaBar['quote'], 'all' => ['label' => text('mega_destinations_all_label'), 'href' => route('destinations.index')]]])
        </div>
    @endif

    {{-- Dropdowns made of image cards (items with children in Admin > Navigation, e.g. About Us). --}}
    @foreach ($menu->where('kind', 'cards') as $item)
        @continue($item->children->isEmpty())
        <div id="mega-{{ $item->id }}" x-show="dd === 'm{{ $item->id }}'" x-cloak x-transition:enter="{{ $megaIn }}" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="{{ $megaPanel }}" @click.outside="dd === 'm{{ $item->id }}' && (dd = null)">
            @include('partials.mega-cards', ['cards' => $item->children->take(6)->map(fn ($c) => ['href' => $c->href(), 'image' => $c->image, 'alt' => $c->image_alt ?? '', 'title' => $c->label, 'text' => $c->description, 'new_tab' => (bool) $c->new_tab]), 'link' => text('nav_card_link_label') ?: text('mega_discover_label')])
            @include('partials.mega-bar', ['bar' => ['tagline' => strip_tags(str_replace('*', '', text('nav_promo_title'))) ?: $megaBar['tagline'], 'packages' => '', 'quote' => text('nav_promo_button') ?: $megaBar['quote']]])
        </div>
    @endforeach

    <nav id="mobile-menu" x-show="menu" x-cloak class="max-h-[80vh] overflow-y-auto border-t border-sand-200 bg-white lg:hidden" aria-label="Mobile">
        <div class="container-site space-y-1 py-3 text-sm font-semibold">
            <a href="{{ route('home') }}" class="block rounded px-2 py-2.5 hover:bg-sand">{{ text('mobile_home_label') }}</a>
            @if ($hasTours)
                <p class="label-caps px-2 pt-3 text-rust">{{ text('mobile_tours_heading') }}</p>
                @foreach ($tourTypes as $t)<a href="{{ route('tour-types.show', $t) }}" class="block rounded px-2 py-2.5 hover:bg-sand">{{ $t->name }}</a>@endforeach
            @endif
            @if ($destinations->isNotEmpty())
                <p class="label-caps px-2 pt-3 text-rust">{{ text('mobile_destinations_heading') }}</p>
                @foreach ($destinations as $d)<a href="{{ route('destinations.show', $d) }}" class="block rounded px-2 py-2.5 hover:bg-sand">{{ $d->name }}</a>@endforeach
            @endif
            <p class="label-caps px-2 pt-3 text-rust">{{ text('mobile_more_heading') }}</p>
            @foreach ($menu->where('kind', '!=', 'tours') as $item)
                <a href="{{ $item->href() }}" class="block rounded px-2 py-2.5 hover:bg-sand" @if ($item->new_tab) target="_blank" rel="noopener" @endif>{{ $item->label }}</a>
                @foreach ($item->children->filter(fn ($c) => $c->href() !== $item->href()) as $c)
                    <a href="{{ $c->href() }}" class="block rounded px-2 py-2.5 pl-5 font-normal hover:bg-sand" @if ($c->new_tab) target="_blank" rel="noopener" @endif>{{ $c->label }}</a>
                @endforeach
            @endforeach
            @if (text('mobile_quote_label'))<button type="button" class="btn btn-primary mt-3 w-full" @click="menu = false; $store.modal.show()">{{ text('mobile_quote_label') }}</button>@endif
        </div>
    </nav>
</header>
