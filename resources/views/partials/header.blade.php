{{-- Sticky header with full-width mega menus. Items come from Admin > Navigation (menu "header"): a "Tours" item shows the
     auto mega menu of tour types, an item with children shows a dropdown of image cards, other items are plain links.
     $tourTypes (with published packages) and $destinations come from the layout. Labels come from Admin > Settings. --}}
@php
    $navBtn = 'hdr-link inline-flex items-center gap-1.5 whitespace-nowrap rounded-lg px-3 py-2.5 text-[0.9rem] font-medium xl:px-4 text-ink transition-colors hover:bg-sand';
    $menu = \App\Models\MenuItem::tree('header');
    $hasTours = $menu->contains('kind', 'tours');
    $overlay = ! empty($overlayHeader);   // the page starts with a photo hero: the header is fully transparent over it (white links, white logo) until you scroll, then it becomes a white bar
@endphp
<header class="hdr sticky top-0 z-50 border-b-4 border-accent bg-white/95 backdrop-blur {{ $overlay ? 'hdr-overlay -mb-[4.25rem] lg:-mb-[5.25rem]' : '' }}"
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

    {{-- Mega menu: Tours (automatic, from Admin > Tour types and Packages). Soft sand backdrop, white cards that lift slightly on hover. --}}
    @if ($hasTours)
    <div id="mega-tours" x-show="dd === 'tours'" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="absolute inset-x-0 top-full hidden border-b-4 border-accent bg-sand shadow-card lg:block" @click.outside="dd === 'tours' && (dd = null)">
        <div class="container-site grid gap-6 py-8" style="grid-template-columns: repeat({{ max($tourTypes->count(), 1) }}, minmax(0, 1fr))">
            @foreach ($tourTypes as $t)
                <article class="group flex flex-col overflow-hidden rounded-2xl bg-white ring-1 ring-sand-200 transition duration-300 hover:-translate-y-0.5 hover:shadow-card">
                    <a href="{{ route('tour-types.show', $t) }}" class="relative block overflow-hidden" @click="dd = null">
                        <x-picture :src="$t->image ?: 'images/tour-self-drive.webp'" :alt="$t->image_alt ?? ''" :width="960" :height="640" sizes="(min-width:1024px) 30vw, 100vw" class="aspect-[16/9] w-full object-cover transition-transform duration-700 group-hover:scale-105" />
                        <span class="absolute inset-0 bg-gradient-to-t from-ink/70 via-ink/10 to-transparent"></span>
                        <span class="absolute left-3 top-3 inline-flex items-center gap-1.5 rounded-full bg-white/90 px-2.5 py-1 text-[0.65rem] font-bold uppercase tracking-wider text-ink"><x-icon :name="$t->icon ?: 'check'" class="h-3.5 w-3.5 text-rust" />{{ $t->packages->count() }} {{ \Illuminate\Support\Str::plural('tour', $t->packages->count()) }}</span>
                        <h3 class="absolute inset-x-4 bottom-3 text-2xl font-medium text-white">{{ $t->name }}</h3>
                    </a>
                    <div class="flex flex-1 flex-col p-4">
                        <p class="line-clamp-2 text-sm text-ink/70">{{ $t->summary }}</p>
                        <ul class="mt-3 text-sm">
                            @foreach ($t->packages->take(4) as $p)
                                <li class="border-t border-sand-200"><a href="{{ route('packages.show', $p) }}" class="group/row flex items-center justify-between gap-3 py-2.5 transition-colors hover:text-rust" @click="dd = null"><span class="min-w-0 truncate">{{ $p->title }}</span><span class="flex shrink-0 items-center gap-2 text-xs text-ink/60">{{ $p->nights }} {{ \Illuminate\Support\Str::plural('night', $p->nights) }}<x-icon name="arrow" class="h-3.5 w-3.5 -translate-x-1 text-rust opacity-0 transition group-hover/row:translate-x-0 group-hover/row:opacity-100" /></span></a></li>
                            @endforeach
                        </ul>
                        <a href="{{ route('tour-types.show', $t) }}" class="label-caps group/all mt-auto inline-flex items-center gap-2 pt-3 text-xs text-rust" @click="dd = null">{{ text('mega_view_all_label') }} {{ $t->name }} <x-icon name="arrow" class="h-4 w-4 transition-transform group-hover/all:translate-x-1" /></a>
                    </div>
                </article>
            @endforeach
        </div>
        <div class="border-t border-sand-200 bg-white">
            <div class="container-site flex items-center justify-between gap-4 py-4">
                <p class="flex items-center gap-2 text-sm font-medium"><span class="flex h-8 w-8 items-center justify-center rounded-full bg-sand text-rust"><x-icon name="heart" class="h-4 w-4" /></span>{{ text('mega_help_text') }}</p>
                <div class="flex gap-3">
                    @if (text('mega_all_packages_label'))<a href="{{ route('packages.index') }}" class="btn btn-outline !py-2.5 text-brand-900" @click="dd = null">{{ text('mega_all_packages_label') }}</a>@endif
                    @if (text('mega_quote_label'))<x-button class="!py-2.5" @click="dd = null; $store.modal.show()">{{ text('mega_quote_label') }}</x-button>@endif
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Dropdown menus made of image cards (items with children in Admin > Navigation).
         Wide screens (xl): intro panel on the left, cards on the right, cards stretch to the intro panel's height.
         Narrower desktops: the intro becomes a slim banner above a full-width row of cards, so nothing is cramped. --}}
    @foreach ($menu->where('kind', 'cards') as $item)
        @continue($item->children->isEmpty())
        @php $cards = $item->children->take(6); @endphp
        <div id="mega-{{ $item->id }}" x-show="dd === 'm{{ $item->id }}'" x-cloak
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="absolute inset-x-0 top-full hidden border-b-4 border-accent bg-sand shadow-card lg:block" @click.outside="dd === 'm{{ $item->id }}' && (dd = null)">
            {{-- Intro panel on the left (a fixed, comfortable width), the cards in a grid of up to three columns on the right. Each card is a
                 horizontal row (square photo + title + short text), so six cards fit in two roomy rows instead of six narrow columns.
                 Narrower desktops: the intro becomes a slim banner above the cards. --}}
            <div class="container-site grid items-stretch gap-5 py-7 xl:grid-cols-[17rem_minmax(0,1fr)] xl:py-8">
                <div class="relative isolate flex items-center justify-between gap-8 overflow-hidden rounded-2xl bg-brand-900 px-7 py-5 text-white xl:flex-col xl:items-start xl:justify-center xl:py-7">
                    <span class="absolute -right-12 -top-12 -z-10 h-44 w-44 rounded-full bg-accent/15" aria-hidden="true"></span>
                    <span class="absolute -bottom-14 -left-10 -z-10 h-40 w-40 rounded-full bg-brand/30" aria-hidden="true"></span>
                    <div>
                        <p class="text-sm font-semibold text-accent">{{ $brand }}</p>
                        <p class="mt-1 font-display text-2xl font-medium leading-snug xl:mt-3">{{ text('nav_promo_title') }}</p>
                        <p class="mt-1 text-sm text-white/80 xl:mt-3">{{ text('nav_promo_text') }}</p>
                    </div>
                    @if (text('nav_promo_button'))<x-button class="shrink-0 whitespace-nowrap !py-2.5 xl:mt-3" @click="dd = null; $store.modal.show()">{{ text('nav_promo_button') }}</x-button>@endif
                </div>
                <div class="grid content-center gap-4" style="grid-template-columns: repeat({{ min($cards->count(), 3) }}, minmax(0, 1fr))">
                    @foreach ($cards as $c)
                        <a href="{{ $c->href() }}" class="group flex items-center gap-4 rounded-2xl bg-white p-3 ring-1 ring-sand-200 transition duration-300 hover:-translate-y-0.5 hover:shadow-card" @click="dd = null" @if ($c->new_tab) target="_blank" rel="noopener" @endif>
                            <span class="relative block h-20 w-20 shrink-0 overflow-hidden rounded-xl bg-brand-900 xl:h-24 xl:w-24">
                                @if ($c->image)<x-picture :src="$c->image" :alt="$c->image_alt ?? ''" :width="480" :height="480" sizes="96px" class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" />@endif
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="flex items-center justify-between gap-2">
                                    <span class="font-display text-lg font-medium leading-tight transition-colors group-hover:text-rust">{{ $c->label }}</span>
                                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-sand text-rust transition-colors group-hover:bg-accent group-hover:text-ink"><x-icon name="arrow" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5" /></span>
                                </span>
                                @if ($c->description)<span class="mt-1 block text-sm leading-snug text-ink/70">{{ $c->description }}</span>@endif
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
