{{-- Sticky header with full-width mega menus (Tours, About Us). $tourTypes (with published packages) and $destinations come from the layout. --}}
@php
    $navBtn = 'inline-flex items-center gap-1.5 rounded-lg px-4 py-2.5 text-sm font-semibold uppercase tracking-wide text-ink transition-colors hover:bg-sand';
    $aboutCards = [
        ['About Us', 'Who we are and how we plan your trip', route('about'), 'images/Intro_image_split.webp', 'A traditional Dutch canal house and windmill'],
        ['Contact', 'Talk to a travel specialist', route('contact'), 'images/Guide_1.webp', 'A travel specialist helping plan a Netherlands trip'],
        ['Testimonials', 'What travellers say about us', route('testimonials'), 'images/couples.webp', 'A happy couple travelling in the Netherlands'],
        ['FAQs', 'Answers to common questions', route('faq'), 'images/feature_band.webp', 'Tulip fields stretching to the horizon'],
    ];
@endphp
<header class="sticky top-0 z-50 border-b-4 border-accent bg-white" x-data="{ menu: false, dd: null }" @keydown.escape.window="menu = false; dd = null" @mouseleave="dd = null">
    <div class="container-site flex h-16 items-center justify-between gap-4 lg:h-20">
        <a href="{{ route('home') }}" @mouseenter="dd = null">@include('partials.logo')</a>

        <nav class="hidden items-center gap-1 lg:flex" aria-label="Main">
            <button type="button" class="{{ $navBtn }}" :class="dd === 'tours' && 'bg-sand'" @click="dd = dd === 'tours' ? null : 'tours'" @mouseenter="dd = 'tours'" :aria-expanded="dd === 'tours'" aria-controls="mega-tours">
                Tours <x-icon name="chevron" class="h-4 w-4 transition-transform" ::class="dd === 'tours' && 'rotate-180'" />
            </button>
            <a href="{{ route('packages.index') }}" class="{{ $navBtn }}" @mouseenter="dd = null">Packages</a>
            <button type="button" class="{{ $navBtn }}" :class="dd === 'about' && 'bg-sand'" @click="dd = dd === 'about' ? null : 'about'" @mouseenter="dd = 'about'" :aria-expanded="dd === 'about'" aria-controls="mega-about">
                About Us <x-icon name="chevron" class="h-4 w-4 transition-transform" ::class="dd === 'about' && 'rotate-180'" />
            </button>
            <a href="{{ route('blog.index') }}" class="{{ $navBtn }}" @mouseenter="dd = null">Blog</a>
            <a href="{{ route('contact') }}" class="{{ $navBtn }}" @mouseenter="dd = null">Contact</a>
        </nav>

        <div class="flex items-center gap-2">
            <x-button class="hidden sm:inline-flex !py-2.5" @click="$store.modal.show()">Free Quote</x-button>
            <button type="button" class="rounded p-2 lg:hidden" @click="menu = !menu" :aria-expanded="menu" aria-controls="mobile-menu" aria-label="Menu">
                <x-icon name="menu" x-show="!menu" /><x-icon name="close" x-show="menu" x-cloak />
            </button>
        </div>
    </div>

    {{-- Mega menu: Tours --}}
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
                    <a href="{{ route('tour-types.show', $t) }}" class="label-caps mt-4 inline-flex items-center gap-2 text-xs text-rust hover:underline" @click="dd = null">View all {{ $t->name }} <x-icon name="arrow" class="h-4 w-4" /></a>
                </div>
            @endforeach
        </div>
        <div class="border-t border-sand-200 bg-sand">
            <div class="container-site flex items-center justify-between gap-4 py-4">
                <p class="text-sm font-medium">Not sure which suits you? We will plan it around you.</p>
                <div class="flex gap-3">
                    <a href="{{ route('packages.index') }}" class="btn btn-outline !py-2.5 text-brand-900" @click="dd = null">All Packages</a>
                    <x-button class="!py-2.5" @click="dd = null; $store.modal.show()">Get a Free Quote</x-button>
                </div>
            </div>
        </div>
    </div>

    {{-- Mega menu: About Us --}}
    <div id="mega-about" x-show="dd === 'about'" x-cloak x-transition.opacity.duration.150ms class="absolute inset-x-0 top-full hidden border-b-4 border-accent bg-white shadow-card lg:block" @click.outside="dd === 'about' && (dd = null)">
        <div class="container-site grid grid-cols-[1.1fr_repeat(4,1fr)] gap-6 py-8">
            <div class="flex flex-col justify-center rounded-2xl bg-brand-900 p-7 text-white">
                <p class="label-caps text-xs text-accent"><span class="mr-2 inline-block h-px w-6 bg-accent align-middle"></span>{{ $brand }}</p>
                <p class="mt-4 font-display text-2xl font-medium leading-snug">Journeys planned around you.</p>
                <p class="mt-3 text-sm text-white/80">Tell us how you like to travel and we will shape the trip.</p>
                <x-button class="mt-6 self-start !py-2.5" @click="dd = null; $store.modal.show()">Get a Free Quote</x-button>
            </div>
            @foreach ($aboutCards as [$label, $sub, $href, $img, $alt])
                <a href="{{ $href }}" class="group block" @click="dd = null">
                    <div class="overflow-hidden rounded-xl"><x-picture :src="$img" :alt="$alt" :width="960" :height="640" class="aspect-[4/3] w-full object-cover transition-transform duration-500 group-hover:scale-105" /></div>
                    <h3 class="mt-3 text-lg font-medium group-hover:text-rust">{{ $label }}</h3>
                    <p class="text-sm text-ink/70">{{ $sub }}</p>
                </a>
            @endforeach
        </div>
    </div>

    <nav id="mobile-menu" x-show="menu" x-cloak class="max-h-[80vh] overflow-y-auto border-t border-sand-200 bg-white lg:hidden" aria-label="Mobile">
        <div class="container-site space-y-1 py-3 text-sm font-semibold">
            <a href="{{ route('home') }}" class="block rounded px-2 py-2.5 hover:bg-sand">Home</a>
            <p class="label-caps px-2 pt-3 text-rust">Tours</p>
            @foreach ($tourTypes as $t)<a href="{{ route('tour-types.show', $t) }}" class="block rounded px-2 py-2.5 hover:bg-sand">{{ $t->name }}</a>@endforeach
            <p class="label-caps px-2 pt-3 text-rust">Destinations</p>
            @foreach ($destinations as $d)<a href="{{ route('destinations.show', $d) }}" class="block rounded px-2 py-2.5 hover:bg-sand">{{ $d->name }}</a>@endforeach
            <p class="label-caps px-2 pt-3 text-rust">More</p>
            <a href="{{ route('packages.index') }}" class="block rounded px-2 py-2.5 hover:bg-sand">All packages</a>
            <a href="{{ route('about') }}" class="block rounded px-2 py-2.5 hover:bg-sand">About Us</a>
            <a href="{{ route('blog.index') }}" class="block rounded px-2 py-2.5 hover:bg-sand">Blog</a>
            <a href="{{ route('faq') }}" class="block rounded px-2 py-2.5 hover:bg-sand">FAQ</a>
            <a href="{{ route('testimonials') }}" class="block rounded px-2 py-2.5 hover:bg-sand">Testimonials</a>
            <a href="{{ route('contact') }}" class="block rounded px-2 py-2.5 hover:bg-sand">Contact</a>
            <button type="button" class="btn btn-primary mt-3 w-full" @click="menu = false; $store.modal.show()">Free Quote</button>
        </div>
    </nav>
</header>
