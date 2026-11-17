{{-- Sticky header with full-width mega menus. Items come from Admin > Navigation (menu "header"): a "Tours" item shows the
     auto mega menu of tour types, an item with children shows a dropdown of image cards, other items are plain links.
     $tourTypes (with published packages) and $destinations come from the layout. Labels come from Admin > Settings. --}}
@php
    $navBtn = 'inline-flex items-center gap-1.5 rounded-lg px-4 py-2.5 text-sm font-semibold uppercase tracking-wide text-ink transition-colors hover:bg-sand';
    $menu = \App\Models\MenuItem::tree('header');
    $hasTours = $menu->contains('kind', 'tours');
@endphp
<header class="sticky top-0 z-50 border-b-4 border-accent bg-white" x-data="{ menu: false, dd: null }" @keydown.escape.window="menu = false; dd = null" @mouseleave="dd = null">
    <div class="container-site flex h-16 items-center justify-between gap-4 lg:h-20">
        <a href="{{ route('home') }}" @mouseenter="dd = null">@include('partials.logo')</a>

        <nav class="hidden items-center gap-1 lg:flex" aria-label="Main">
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
            @if (text('header_button_label'))<x-button class="hidden sm:inline-flex !py-2.5" @click="$store.modal.show()">{{ text('header_button_label') }}</x-button>@endif
            <button type="button" class="rounded p-2 lg:hidden" @click="menu = !menu" :aria-expanded="menu" aria-controls="mobile-menu" aria-label="Menu">
                <x-icon name="menu" x-show="!menu" /><x-icon name="close" x-show="menu" x-cloak />
            </button>
        </div>
    </div>

    {{-- Mega menu: Tours (automatic, from Admin > Tour types and Packages) --}}
    @if ($hasTours)
    <div id="mega-tours" x-show="dd === 'tours'" x-cloak x-transition.opacity.duration.150ms class="absolute inset-x-0 top-full hidden border-b-4 border-accent bg-white shadow-card lg:block" @click.outside="dd === 'tours' && (dd = null)">
        <div class="container-site grid gap-8 py-8 xl:grid-cols-3" style="grid-template-columns: repeat({{ max($tourTypes->count(), 1) }}, minmax(0, 1fr))">
            @foreach ($tourTypes as $t)
                <div>
                    <a href="{{ route('tour-types.show', $t) }}" class="group block" @click="dd = null">
                        <div class="overflow-hidden rounded-xl">
                            <x-picture :src="$t->image ?: 'images/tour-self-drive.webp'" :alt="$t->image_alt ?? ''" :width="960" :height="640" class="aspect-[16/9] w-full object-cover transition-transform duration-500 group-hover:scale-105" />
                        </div>
                        <h3 class="mt-4 text-2xl font-medium group-hover:text-rust">{{ $t->name }}</h3>
                    </a>
                    <p class="mt-1 line-clamp-2 text-sm text-ink/70">{{ $t->summary }}</p>
                    <ul class="mt-4 divide-y divide-sand-200 border-t border-accent/60 text-sm">
                        @foreach ($t->packages->take(4) as $p)
                            <li><a href="{{ route('packages.show', $p) }}" class="flex items-center justify-between gap-3 py-2.5 hover:text-rust" @click="dd = null"><span>{{ $p->title }}</span><span class="shrink-0 text-xs text-ink/60">{{ $p->nights }} nights</span></a></li>
                        @endforeach
                    </ul>
                    <a href="{{ route('tour-types.show', $t) }}" class="label-caps mt-4 inline-flex items-center gap-2 text-xs text-rust hover:underline" @click="dd = null">{{ text('mega_view_all_label') }} {{ $t->name }} <x-icon name="arrow" class="h-4 w-4" /></a>
                </div>
            @endforeach
        </div>
        <div class="border-t border-sand-200 bg-sand">
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

    {{-- Dropdown menus made of image cards (items with children in Admin > Navigation) --}}
    @foreach ($menu->where('kind', 'cards') as $item)
        @continue($item->children->isEmpty())
        <div id="mega-{{ $item->id }}" x-show="dd === 'm{{ $item->id }}'" x-cloak x-transition.opacity.duration.150ms class="absolute inset-x-0 top-full hidden border-b-4 border-accent bg-white shadow-card lg:block" @click.outside="dd === 'm{{ $item->id }}' && (dd = null)">
            <div class="container-site grid gap-6 py-8" style="grid-template-columns: 1.1fr repeat({{ min($item->children->count(), 4) }}, minmax(0, 1fr))">
                <div class="flex flex-col justify-center rounded-2xl bg-brand-900 p-7 text-white">
                    <p class="label-caps text-xs text-accent"><span class="mr-2 inline-block h-px w-6 bg-accent align-middle"></span>{{ $brand }}</p>
                    <p class="mt-4 font-display text-2xl font-medium leading-snug">{{ text('nav_promo_title') }}</p>
                    <p class="mt-3 text-sm text-white/80">{{ text('nav_promo_text') }}</p>
                    @if (text('nav_promo_button'))<x-button class="mt-6 self-start !py-2.5" @click="dd = null; $store.modal.show()">{{ text('nav_promo_button') }}</x-button>@endif
                </div>
                @foreach ($item->children->take(4) as $c)
                    <a href="{{ $c->href() }}" class="group block" @click="dd = null" @if ($c->new_tab) target="_blank" rel="noopener" @endif>
                        @if ($c->image)<div class="overflow-hidden rounded-xl"><x-picture :src="$c->image" :alt="$c->image_alt ?? ''" :width="960" :height="640" class="aspect-[4/3] w-full object-cover transition-transform duration-500 group-hover:scale-105" /></div>@endif
                        <h3 class="mt-3 text-lg font-medium group-hover:text-rust">{{ $c->label }}</h3>
                        @if ($c->description)<p class="text-sm text-ink/70">{{ $c->description }}</p>@endif
                    </a>
                @endforeach
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
