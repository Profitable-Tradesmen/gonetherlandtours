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

    {{-- Mega menu "Tour types" (automatic, from Admin > Tour types and Packages): one column per tour type with a large photo card
         (title on the photo, round arrow button), a short line, two featured trips with a thumbnail, and a "view all" link. A call bar sits below. --}}
    @if ($hasTours)
    <div id="mega-tours" x-show="dd === 'tours'" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="absolute inset-x-0 top-full hidden bg-sand shadow-[0_14px_24px_-10px_rgb(12_13_17/0.3)] lg:block" @click.outside="dd === 'tours' && (dd = null)">
        <div class="container-site grid gap-x-8 gap-y-6 py-8" style="grid-template-columns: repeat({{ max($tourTypes->count(), 1) }}, minmax(0, 1fr))">
            @foreach ($tourTypes as $t)
                <div class="flex min-w-0 flex-col">
                    <a href="{{ route('tour-types.show', $t) }}" class="group relative block overflow-hidden rounded-2xl shadow-card" @click="dd = null">
                        <x-picture :src="$t->image ?: 'images/tour-self-drive.webp'" :alt="$t->image_alt ?? ''" :width="960" :height="640" sizes="(min-width:1024px) 30vw, 100vw" class="aspect-[2/1] w-full object-cover transition-transform duration-700 group-hover:scale-105" />
                        <span class="absolute inset-0 bg-gradient-to-t from-ink/80 via-ink/15 to-transparent"></span>
                        <span class="absolute inset-x-5 bottom-4 flex items-end justify-between gap-3">
                            <span class="font-display text-3xl font-medium leading-none text-white">{{ $t->name }}</span>
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-accent text-ink transition-transform group-hover:translate-x-0.5"><x-icon name="arrow" class="h-5 w-5" /></span>
                        </span>
                    </a>
                    <p class="mt-4 line-clamp-2 min-h-[3rem] text-ink/75">{{ $t->style_note ?: $t->summary }}</p>
                    <p class="label-caps mt-4 flex items-center gap-3 text-xs text-rust"><span class="h-px w-7 bg-accent" aria-hidden="true"></span>{{ text('mega_featured_label') ?: 'Featured trips' }}</p>
                    <ul class="mt-3">
                        @forelse ($t->packages->take(2) as $p)
                            @php $cover = $p->relationLoaded('images') ? $p->images->first() : null; @endphp
                            <li class="border-b border-sand-200 last:border-0">
                                <a href="{{ route('packages.show', $p) }}" class="group/row flex items-center gap-4 py-3 transition-colors hover:text-rust" @click="dd = null">
                                    <span class="block h-14 w-14 shrink-0 overflow-hidden rounded-xl bg-brand-900">
                                        @if ($cover)<x-picture :media="$cover" :alt="$cover->alt" sizes="56px" class="h-full w-full object-cover" />@else<x-picture :src="$t->image ?: 'images/tour-self-drive.webp'" alt="" :width="160" :height="160" sizes="56px" class="h-full w-full object-cover" />@endif
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block truncate font-semibold">{{ $p->title }}</span>
                                        <span class="block truncate text-sm text-ink/60">{{ $p->nights }} {{ \Illuminate\Support\Str::plural('night', $p->nights) }}@if ($p->destination) · {{ $p->destination->name }}@endif</span>
                                    </span>
                                </a>
                            </li>
                        @empty
                            <li class="py-3 text-sm text-ink/60">{{ text('mega_help_text') }}</li>
                        @endforelse
                    </ul>
                    <a href="{{ route('tour-types.show', $t) }}" class="label-caps group/all mt-auto inline-flex items-center gap-3 self-start pt-4 text-xs text-brand-900" @click="dd = null"><span class="border-b-2 border-accent pb-1">{{ text('mega_view_all_label') }}</span><x-icon name="arrow" class="h-4 w-4 transition-transform group-hover/all:translate-x-1" /></a>
                </div>
            @endforeach
        </div>
        <div class="border-t border-sand-200 bg-accent/15">
            <div class="container-site flex items-center justify-between gap-4 py-4">
                <p class="text-sm font-medium">{{ text('mega_help_text') }}</p>
                <div class="flex gap-3">
                    @if (text('mega_all_packages_label'))<a href="{{ route('packages.index') }}" class="btn btn-outline !py-2.5 text-brand-900" @click="dd = null">{{ text('mega_all_packages_label') }}</a>@endif
                    @if (text('mega_quote_label'))<x-button class="!py-2.5" @click="dd = null; $store.modal.show()">{{ text('mega_quote_label') }}</x-button>@endif
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Dropdown menus made of image cards (items with children in Admin > Navigation): a photo intro panel on the left (the menu item's own
         photo, or a default) and the cards on the right, each a photo on top with its title, description and a "learn more" link. --}}
    @foreach ($menu->where('kind', 'cards') as $item)
        @continue($item->children->isEmpty())
        @php $cards = $item->children->take(6); $many = $cards->count() > 4; @endphp
        <div id="mega-{{ $item->id }}" x-show="dd === 'm{{ $item->id }}'" x-cloak
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="absolute inset-x-0 top-full hidden bg-sand shadow-[0_14px_24px_-10px_rgb(12_13_17/0.3)] lg:block" @click.outside="dd === 'm{{ $item->id }}' && (dd = null)">
            <div class="container-site grid items-stretch gap-6 py-7 xl:grid-cols-[19rem_minmax(0,1fr)] xl:py-8">
                <div class="relative isolate flex flex-col justify-end overflow-hidden rounded-2xl bg-brand-900 p-7 text-white xl:min-h-[20rem]">
                    <x-picture :src="$item->image ?: 'images/hero-about.webp'" alt="" :width="960" :height="640" sizes="320px" class="absolute inset-0 -z-20 h-full w-full object-cover" />
                    <span class="absolute inset-0 -z-10 bg-gradient-to-t from-ink/85 via-ink/55 to-ink/30"></span>
                    <p class="label-caps flex items-center gap-3 text-xs text-accent"><span class="h-px w-7 bg-accent" aria-hidden="true"></span>{{ text('nav_promo_eyebrow') ?: $brand }}</p>
                    <p class="mt-3 font-display text-3xl font-medium leading-tight">{!! emphasize(text('nav_promo_title'), 'text-accent') !!}</p>
                    <p class="mt-3 text-sm leading-relaxed text-white/90">{{ text('nav_promo_text') }}</p>
                    @if (text('nav_promo_button'))<x-button class="mt-5 self-start whitespace-nowrap !py-2.5" @click="dd = null; $store.modal.show()">{{ text('nav_promo_button') }}</x-button>@endif
                </div>
                <div class="grid content-start gap-5" style="grid-template-columns: repeat({{ $many ? 3 : max($cards->count(), 1) }}, minmax(0, 1fr))">
                    @foreach ($cards as $c)
                        <a href="{{ $c->href() }}" class="group flex flex-col overflow-hidden rounded-2xl bg-white ring-1 ring-sand-200 transition duration-300 hover:-translate-y-0.5 hover:shadow-card" @click="dd = null" @if ($c->new_tab) target="_blank" rel="noopener" @endif>
                            <span class="relative block overflow-hidden bg-brand-900">
                                @if ($c->image)<x-picture :src="$c->image" :alt="$c->image_alt ?? ''" :width="640" :height="480" sizes="(min-width:1280px) 20vw, 30vw" class="w-full object-cover transition-transform duration-700 group-hover:scale-105 {{ $many ? 'aspect-[16/8]' : 'aspect-[4/3]' }}" />@else<span class="block {{ $many ? 'aspect-[16/8]' : 'aspect-[4/3]' }}"></span>@endif
                            </span>
                            <span class="flex flex-1 flex-col p-4">
                                <span class="font-display text-xl font-medium leading-tight transition-colors group-hover:text-rust">{{ $c->label }}</span>
                                @if ($c->description)<span class="mt-1.5 text-sm leading-snug text-ink/70">{{ $c->description }}</span>@endif
                                <span class="label-caps mt-auto inline-flex items-center gap-2 pt-3 text-[0.7rem] text-rust">{{ text('nav_card_link_label') ?: 'Learn more' }} <x-icon name="arrow" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-1" /></span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
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
