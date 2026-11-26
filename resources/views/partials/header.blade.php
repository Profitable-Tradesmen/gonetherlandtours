{{-- Sticky header with full-width mega menus. Items come from Admin > Navigation (menu "header"): a "Tours" item shows the
     auto mega menu of tour types, an item with children shows a dropdown of image cards, other items are plain links.
     $tourTypes (with published packages) and $destinations come from the layout. Labels come from Admin > Settings. --}}
@php
    $navBtn = 'hdr-link inline-flex items-center gap-1.5 whitespace-nowrap rounded-lg px-2 py-2.5 text-[0.9rem] font-medium xl:px-3 2xl:px-4 text-ink transition-colors hover:bg-sand';
    $menu = \App\Models\MenuItem::tree('header');
    $hasTours = $menu->contains('kind', 'tours');
    $styles = \App\Support\SiteData::travelStyles();
    $overlay = ! empty($overlayHeader);   // the page starts with a photo hero: the header is fully transparent over it (white links, white logo) until you scroll, then it becomes a white bar
@endphp
<header class="hdr sticky top-0 z-50 bg-white/95 shadow-[0_6px_20px_-6px_rgb(12_13_17/0.28)] backdrop-blur {{ $overlay ? 'hdr-overlay -mb-16 lg:-mb-20' : '' }}"
        x-data="{ menu: false, dd: null, solid: false }" x-init="solid = window.scrollY > 24"
        @scroll.window.passive="solid = window.scrollY > 24"
        :class="{ 'is-solid': solid || menu || dd }"
        @keydown.escape.window="menu = false; dd = null" @mouseleave="dd = null">
    <div class="container-site flex h-16 items-center justify-between gap-4 lg:h-20">
        <a href="{{ route('home') }}" aria-label="{{ setting('brand_name', 'GoNetherlandTours') }}" @mouseenter="dd = null"><span class="hdr-logo inline-block">@include('partials.logo')</span></a>

        <nav class="hidden items-center gap-1 lg:ml-auto lg:flex" aria-label="Main">
            @foreach ($menu as $item)
                @if ($item->kind === 'tours')
                    <button type="button" class="{{ $navBtn }}" :class="dd === 'tours' && 'bg-sand'" @click="dd = dd === 'tours' ? null : 'tours'" @mouseenter="dd = 'tours'" :aria-expanded="dd === 'tours'" aria-controls="mega-tours">
                        {{ $item->label }} <x-icon name="chevron" class="h-4 w-4 transition-transform" ::class="dd === 'tours' && 'rotate-180'" />
                    </button>
                @elseif ($item->kind === 'styles' && $styles->isNotEmpty())
                    <button type="button" class="{{ $navBtn }}" :class="dd === 'styles' && 'bg-sand'" @click="dd = dd === 'styles' ? null : 'styles'" @mouseenter="dd = 'styles'" :aria-expanded="dd === 'styles'" aria-controls="mega-styles">
                        {{ $item->label }} <x-icon name="chevron" class="h-4 w-4 transition-transform" ::class="dd === 'styles' && 'rotate-180'" />
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
                    <a href="{{ $item->href() }}" class="{{ $navBtn }} {{ $item->href() === url('/') ? 'lg:!hidden xl:!inline-flex' : '' }}" @if ($item->new_tab) target="_blank" rel="noopener" @endif @mouseenter="dd = null">{{ $item->label }}</a>
                @endif
            @endforeach
        </nav>

        <div class="flex items-center gap-2">
            @if (text('header_button_label'))<x-button class="hidden shrink-0 sm:inline-flex !py-2.5 lg:ml-2" @click="$store.modal.show()">{{ text('header_button_label') }}</x-button>@endif
            <button type="button" class="hdr-link inline-flex h-11 w-11 items-center justify-center rounded text-ink hover:bg-sand lg:hidden" @click="menu = !menu" :aria-expanded="menu" aria-controls="mobile-menu" aria-label="Menu">
                <x-icon name="menu" x-show="!menu" /><x-icon name="close" x-show="menu" x-cloak />
            </button>
        </div>
    </div>

    {{-- Mega menus: light full-width panels with a gold top line, laid out like the sister Go Austria site (links list, one featured card, a navy "Get in touch" card).
         All content is automatic or comes from Admin > Navigation, Tour types, Destinations, the Blog and Settings. --}}
    @php
        $megaPanel = 'absolute inset-x-0 top-full hidden border-t-[3px] border-accent bg-sand text-ink shadow-[0_18px_28px_-12px_rgb(12_13_17/0.4)] lg:block';
        $megaIn = 'transition ease-out duration-200';
        $latestPost = \App\Models\BlogPost::published()->first();
        $postCard = $latestPost ? ['eyebrow' => 'From the journal', 'href' => route('blog.show', $latestPost), 'src' => $latestPost->image ?: 'images/feature_band.webp', 'alt' => $latestPost->image_alt ?? '', 'tag' => $latestPost->label, 'title' => $latestPost->title, 'meta' => null, 'cta' => 'Read article'] : null;
        $megaQuote = text('mega_quote_label') ?: 'Request a quote';
    @endphp

    @php $tripCard = null; @endphp
    {{-- Tour types: three photo cards (one per way to travel) and the personal planning card. --}}
    @if ($hasTours)
        @php
            $trips = $tourTypes->flatMap(fn ($t) => $t->packages->each(fn ($p) => $p->setRelation('tourType', $t)))->unique('id');   // tour type is already loaded: no extra query per package
            $featured = $trips->where('is_featured', true)->first() ?: $trips->first();
            $fc = $featured && $featured->relationLoaded('images') ? $featured->images->first() : null;
            $tripCard = $featured ? ['eyebrow' => text('mega_featured_label') ?: 'Featured trip', 'href' => route('packages.show', $featured), 'media' => $fc, 'src' => $featured->tourType?->image ?: 'images/tour-self-drive.webp', 'alt' => '', 'tag' => $featured->tourType?->name, 'title' => $featured->title,
                'meta' => $featured->nights.' '.\Illuminate\Support\Str::plural('night', $featured->nights).($featured->price_from ? ' · From '.$featured->priceLabel() : ''), 'cta' => 'View trip'] : null;
        @endphp
        <div id="mega-tours" x-show="dd === 'tours'" x-cloak x-transition:enter="{{ $megaIn }}" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="{{ $megaPanel }}" @click.outside="dd === 'tours' && (dd = null)">
            @include('partials.mega-tours', ['megaQuote' => $megaQuote])
        </div>
    @endif

    {{-- Travel styles: a photo card per interest and the planning card. --}}
    @if ($styles->isNotEmpty() && $menu->contains('kind', 'styles'))
        <div id="mega-styles" x-show="dd === 'styles'" x-cloak x-transition:enter="{{ $megaIn }}" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="{{ $megaPanel }}" @click.outside="dd === 'styles' && (dd = null)">
            @include('partials.mega-styles', ['megaQuote' => $megaQuote])
        </div>
    @endif

    {{-- Destinations: the regions, the latest journal post, and the contact card. --}}
    @if ($destinations->isNotEmpty())
        <div id="mega-destinations" x-show="dd === 'dest'" x-cloak x-transition:enter="{{ $megaIn }}" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="{{ $megaPanel }}" @click.outside="dd === 'dest' && (dd = null)">
            @include('partials.mega-destinations', ['tripCard' => $tripCard ?? null, 'megaQuote' => $megaQuote])
        </div>
    @endif

    {{-- Dropdowns for items with children in Admin > Navigation (e.g. About us): the links, the latest journal post, and the contact card. --}}
    @foreach ($menu->where('kind', 'cards') as $item)
        @continue($item->children->isEmpty())
        <div id="mega-{{ $item->id }}" x-show="dd === 'm{{ $item->id }}'" x-cloak x-transition:enter="{{ $megaIn }}" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="{{ $megaPanel }}" @click.outside="dd === 'm{{ $item->id }}' && (dd = null)">
            @include('partials.mega-panel', ['eyebrow' => $item->label,
                'items' => $item->children->take(6)->map(fn ($c) => ['href' => $c->href(), 'title' => $c->label, 'text' => $c->description, 'new_tab' => (bool) $c->new_tab]),
                'foot' => null, 'legal' => [['Terms and conditions', route('terms')], ['Privacy policy', route('privacy')]], 'card' => $postCard, 'quote' => $megaQuote])
        </div>
    @endforeach

    <nav id="mobile-menu" x-show="menu" x-cloak class="max-h-[80vh] overflow-y-auto border-t border-sand-200 bg-white lg:hidden" aria-label="Mobile">
        <div class="container-site space-y-1 py-3 text-sm font-semibold">
            <a href="{{ route('home') }}" class="block rounded px-2 py-2.5 hover:bg-sand">{{ text('mobile_home_label') }}</a>
            @if ($hasTours)
                <p class="label-caps px-2 pt-3 text-rust">{{ text('mobile_tours_heading') }}</p>
                @foreach ($tourTypes as $t)<a href="{{ route('tour-types.show', $t) }}" class="block rounded px-2 py-2.5 hover:bg-sand">{{ $t->name }}</a>@endforeach
            @endif
            @if ($styles->isNotEmpty() && $menu->contains('kind', 'styles'))
                <p class="label-caps px-2 pt-3 text-rust">Travel styles</p>
                @foreach ($styles as $s)<a href="{{ route('travel-styles.show', $s) }}" class="block rounded px-2 py-2.5 hover:bg-sand">{{ $s->name }}</a>@endforeach
            @endif
            @if ($destinations->isNotEmpty())
                <p class="label-caps px-2 pt-3 text-rust">{{ text('mobile_destinations_heading') }}</p>
                @foreach ($destinations as $d)<a href="{{ route('destinations.show', $d) }}" class="block rounded px-2 py-2.5 hover:bg-sand">{{ $d->name }}</a>@endforeach
            @endif
            <p class="label-caps px-2 pt-3 text-rust">{{ text('mobile_more_heading') }}</p>
            @foreach ($menu->whereNotIn('kind', ['tours', 'styles']) as $item)
                <a href="{{ $item->href() }}" class="block rounded px-2 py-2.5 hover:bg-sand" @if ($item->new_tab) target="_blank" rel="noopener" @endif>{{ $item->label }}</a>
                @foreach ($item->children->filter(fn ($c) => $c->href() !== $item->href()) as $c)
                    <a href="{{ $c->href() }}" class="block rounded px-2 py-2.5 pl-5 font-normal hover:bg-sand" @if ($c->new_tab) target="_blank" rel="noopener" @endif>{{ $c->label }}</a>
                @endforeach
            @endforeach
            @if (text('mobile_quote_label'))<button type="button" class="btn btn-primary mt-3 w-full" @click="menu = false; $store.modal.show()">{{ text('mobile_quote_label') }}</button>@endif
        </div>
    </nav>
</header>
