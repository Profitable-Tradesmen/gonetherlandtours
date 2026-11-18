@php
    $cover = $package->images->first();
    $fallback = $package->tourType?->image ?: 'images/tour-self-drive.webp';
    $hasPrice = (bool) $package->price_from;
    $badge = $package->is_sample ? 'Sample' : null;
    $url = route('packages.show', $package);
@endphp
{{-- Package card: image on top at every screen size (a side-by-side layout squeezed the text on phones). Real data only. --}}
<article class="group flex h-full flex-col overflow-hidden rounded-2xl bg-brand-900 text-white shadow-card">
    <a href="{{ $url }}" class="relative block w-full overflow-hidden" tabindex="-1" aria-hidden="true">
        @if ($cover)
            <x-picture :media="$cover" :alt="$cover->alt" class="aspect-[3/2] h-auto w-full object-cover transition-transform duration-500 group-hover:scale-105" sizes="(min-width:1024px) 33vw, (min-width:640px) 50vw, 84vw" />
        @else
            <x-picture :src="$fallback" :alt="$package->tourType?->image_alt ?? ''" :width="960" :height="640" class="aspect-[3/2] h-auto w-full object-cover transition-transform duration-500 group-hover:scale-105" />
        @endif
        @if ($badge)
            <span class="label-caps absolute right-0 top-4 whitespace-nowrap bg-accent px-3 py-1.5 text-[0.65rem] leading-tight text-ink sm:px-4 sm:text-xs">{{ $badge }}</span>
        @endif
    </a>

    <div class="flex flex-1 flex-col p-5 sm:p-6">
        <div class="flex flex-wrap items-center gap-2 text-[0.68rem] font-bold uppercase tracking-wider">
            <span class="rounded-full bg-accent px-2.5 py-1 text-ink">{{ $package->nights }} {{ \Illuminate\Support\Str::plural('night', $package->nights) }}</span>
            @if ($package->tourType)<span class="rounded-full bg-white/15 px-2.5 py-1 text-white">{{ $package->tourType->name }}</span>@endif
            @if ($package->destination)<span class="rounded-full border border-white/30 px-2.5 py-1 text-white/90">{{ $package->destination->name }}</span>@endif
        </div>
        <h3 class="mt-3 text-xl font-medium leading-snug text-white sm:text-2xl"><a href="{{ $url }}" class="hover:text-accent">{{ $package->title }}</a></h3>
        <p class="mt-2 line-clamp-2 text-sm leading-relaxed text-white/80 sm:line-clamp-3">{{ $package->summary }}</p>

        <div class="mt-4 border-t border-white/20 pt-4">
            <p class="label-caps text-[0.65rem] text-white/60">From</p>
            @if ($hasPrice)
                <p class="font-display text-2xl font-medium text-accent sm:text-3xl">{{ $package->priceLabel() }}<span class="ml-1 text-xs font-semibold text-accent/80">pp</span></p>
            @else
                <p class="font-display text-xl font-medium text-accent">{{ $package->priceLabel() }}</p>
            @endif
        </div>

        <div class="mt-auto grid grid-cols-2 gap-3 pt-5">
            <button type="button" class="btn btn-primary w-full !px-3" @click="$store.modal.show({ id: {{ $package->id }}, name: @js($package->title) })">Get a Quote</button>
            <a href="{{ $url }}" class="btn btn-outline w-full !px-3 text-white">More info<span class="sr-only"> about {{ $package->title }}</span></a>
        </div>
    </div>
</article>
