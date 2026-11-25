{{-- Destinations mega menu (layout from the sister Go Austria site): tall photo cards for the regions, a two-column list of popular places, and one featured trip.
     Expects $destinations, $tourTypes (with packages loaded), $tripCard (see header), $megaQuote. Everything is automatic. --}}
@php
    $dests = $destinations->take(4);
    $packagesAll = $tourTypes->flatMap->packages->unique('id');
    $stops = collect(\App\Support\NetherlandsMap::STOPS)->filter(fn ($s) => $destinations->contains('slug', $s[3]))->values();
    $slugToDest = $destinations->keyBy('slug');
    $half = (int) ceil($stops->count() / 2);
@endphp
<div class="container-site grid gap-x-10 gap-y-8 pb-9 pt-8 lg:grid-cols-[minmax(0,1.45fr)_minmax(0,1fr)_minmax(0,0.8fr)]">
    <div class="min-w-0">
        <p class="label-caps text-xs font-bold text-rust">Explore by region</p>
        <div class="mt-5 grid gap-4" style="grid-template-columns: repeat({{ max(1, min($dests->count(), 3)) }}, minmax(0, 1fr))">
            @foreach ($dests->take(3) as $dest)
                @php $count = $packagesAll->where('destination_id', $dest->id)->count(); @endphp
                <a href="{{ route('destinations.show', $dest) }}" class="group relative isolate flex aspect-[4/5] flex-col justify-end overflow-hidden rounded-lg bg-brand-900 p-4 text-white" @click="dd = null">
                    @if ($dest->image)<x-picture :src="$dest->image" :alt="''" :width="640" :height="800" sizes="220px" class="absolute inset-0 -z-20 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" />@endif
                    <span class="absolute inset-0 -z-10 bg-gradient-to-t from-ink/85 via-ink/20 to-transparent"></span>
                    <span class="font-display text-xl font-medium leading-tight">{{ $dest->name }}</span>
                    <span class="label-caps mt-1 text-[0.65rem] font-bold text-accent">{{ $count }} {{ \Illuminate\Support\Str::plural('tour', $count) }}</span>
                </a>
            @endforeach
        </div>
    </div>

    <div class="min-w-0">
        <p class="label-caps text-xs font-bold text-rust">Popular places</p>
        <div class="mt-5 grid grid-cols-2 gap-x-6">
            @foreach ([$stops->take($half), $stops->slice($half)] as $col)
                <ul>
                    @foreach ($col as $stop)
                        <li class="border-t border-sand-200 first:border-0">
                            <a href="{{ route('destinations.show', $slugToDest[$stop[3]]) }}" class="group flex min-h-[2.75rem] items-center gap-2.5 py-2 text-ink" @click="dd = null">
                                <i class="h-1.5 w-1.5 shrink-0 rotate-45 bg-accent" aria-hidden="true"></i>
                                <span class="font-display text-lg leading-tight transition-colors group-hover:text-rust">{{ $stop[0] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endforeach
        </div>
        <a href="{{ route('destinations.index') }}" class="label-caps mt-4 inline-flex items-center gap-2 border-b-2 border-accent pb-0.5 text-[0.7rem] font-bold text-brand-900 hover:text-rust" @click="dd = null">{{ text('mega_destinations_all_label') ?: 'All destinations' }} <x-icon name="arrow" class="h-3.5 w-3.5" /></a>
    </div>

    @if ($tripCard)
        <div class="min-w-0 self-start">
            <a href="{{ $tripCard['href'] }}" class="group block overflow-hidden border-t-[3px] border-accent bg-white shadow-card" @click="dd = null">
                <span class="relative block overflow-hidden">
                    @if (! empty($tripCard['media']))<x-picture :media="$tripCard['media']" :alt="$tripCard['media']->alt" sizes="300px" class="aspect-[16/10] w-full object-cover transition-transform duration-700 group-hover:scale-105" />@else<x-picture :src="$tripCard['src']" :alt="''" :width="640" :height="400" sizes="300px" class="aspect-[16/10] w-full object-cover transition-transform duration-700 group-hover:scale-105" />@endif
                    @if (! empty($tripCard['tag']))<span class="label-caps absolute left-3 top-3 bg-accent px-3 py-1.5 text-[0.65rem] font-bold text-ink">{{ $tripCard['tag'] }}</span>@endif
                </span>
                <span class="block p-5">
                    <span class="label-caps block text-[0.65rem] font-bold text-rust">{{ $tripCard['eyebrow'] }}</span>
                    <span class="mt-1 block font-display text-xl font-medium leading-snug text-brand-900">{{ $tripCard['title'] }}</span>
                    @if (! empty($tripCard['meta']))<span class="mt-1.5 block text-sm text-ink/70">{{ $tripCard['meta'] }}</span>@endif
                    <span class="btn btn-primary mt-4 !px-5 !py-2.5">View trip <x-icon name="arrow" class="h-4 w-4" /></span>
                </span>
            </a>
        </div>
    @endif
</div>
