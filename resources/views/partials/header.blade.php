{{-- Sticky header: logo + nav (dropdowns for tour types and destinations) + mobile menu. $tourTypes/$destinations come from the layout. --}}
<header class="sticky top-0 z-50 border-b border-sand-200 bg-white" x-data="{ menu: false, dd: null }" @keydown.escape.window="menu = false; dd = null">
    <div class="container-site flex h-16 items-center justify-between gap-4">
        <a href="{{ route('home') }}">@include('partials.logo')</a>

        <nav class="hidden items-center gap-1 lg:flex" aria-label="Main">
            <a href="{{ route('home') }}" class="rounded px-3 py-2 text-sm font-semibold hover:text-rust">Home</a>

            <div class="relative" @mouseleave="dd = null">
                <button type="button" class="inline-flex items-center gap-1 rounded px-3 py-2 text-sm font-semibold hover:text-rust" @click="dd = dd === 'tours' ? null : 'tours'" @mouseenter="dd = 'tours'" :aria-expanded="dd === 'tours'">Tour Types <x-icon name="chevron" class="h-4 w-4" /></button>
                <div x-show="dd === 'tours'" x-cloak class="absolute left-0 top-full w-60 rounded-xl border border-sand-200 bg-white p-2 shadow-card" @click.outside="dd = null">
                    @foreach ($tourTypes as $t)<a href="{{ route('packages.index', ['type' => $t->slug]) }}" class="block rounded-lg px-3 py-2 text-sm hover:bg-sand">{{ $t->name }}</a>@endforeach
                </div>
            </div>

            <div class="relative" @mouseleave="dd = null">
                <button type="button" class="inline-flex items-center gap-1 rounded px-3 py-2 text-sm font-semibold hover:text-rust" @click="dd = dd === 'dest' ? null : 'dest'" @mouseenter="dd = 'dest'" :aria-expanded="dd === 'dest'">Destinations <x-icon name="chevron" class="h-4 w-4" /></button>
                <div x-show="dd === 'dest'" x-cloak class="absolute left-0 top-full w-64 rounded-xl border border-sand-200 bg-white p-2 shadow-card" @click.outside="dd = null">
                    @foreach ($destinations as $d)<a href="{{ route('destinations.show', $d) }}" class="block rounded-lg px-3 py-2 text-sm hover:bg-sand">{{ $d->name }}</a>@endforeach
                </div>
            </div>

            <a href="{{ route('packages.index') }}" class="rounded px-3 py-2 text-sm font-semibold hover:text-rust">Packages</a>
            <a href="{{ route('about') }}" class="rounded px-3 py-2 text-sm font-semibold hover:text-rust">About</a>
            <a href="{{ route('contact') }}" class="rounded px-3 py-2 text-sm font-semibold hover:text-rust">Contact</a>
        </nav>

        <div class="flex items-center gap-2">
            <x-button class="hidden sm:inline-flex !py-2" @click="$store.modal.show()">Get a Quote</x-button>
            <button type="button" class="rounded p-2 lg:hidden" @click="menu = !menu" :aria-expanded="menu" aria-controls="mobile-menu" aria-label="Menu">
                <x-icon name="menu" x-show="!menu" /><x-icon name="close" x-show="menu" x-cloak />
            </button>
        </div>
    </div>

    <nav id="mobile-menu" x-show="menu" x-cloak class="max-h-[80vh] overflow-y-auto border-t border-sand-200 bg-white lg:hidden" aria-label="Mobile">
        <div class="container-site space-y-1 py-3 text-sm font-semibold">
            <a href="{{ route('home') }}" class="block rounded px-2 py-2.5 hover:bg-sand">Home</a>
            <p class="label-caps px-2 pt-3 text-rust">Tour types</p>
            @foreach ($tourTypes as $t)<a href="{{ route('packages.index', ['type' => $t->slug]) }}" class="block rounded px-2 py-2.5 hover:bg-sand">{{ $t->name }}</a>@endforeach
            <p class="label-caps px-2 pt-3 text-rust">Destinations</p>
            @foreach ($destinations as $d)<a href="{{ route('destinations.show', $d) }}" class="block rounded px-2 py-2.5 hover:bg-sand">{{ $d->name }}</a>@endforeach
            <p class="label-caps px-2 pt-3 text-rust">More</p>
            <a href="{{ route('packages.index') }}" class="block rounded px-2 py-2.5 hover:bg-sand">All packages</a>
            <a href="{{ route('about') }}" class="block rounded px-2 py-2.5 hover:bg-sand">About</a>
            <a href="{{ route('contact') }}" class="block rounded px-2 py-2.5 hover:bg-sand">Contact</a>
        </div>
    </nav>
</header>
