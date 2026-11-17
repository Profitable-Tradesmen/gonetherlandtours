@php
    $cover = $package->images->first();
    $fallback = $package->tourType?->image ?: 'images/tour-self-drive.webp';
    $hasPrice = (bool) $package->price_from;
    $badge = $package->is_sample ? 'Sample' : $package->destination?->name;
    $url = route('packages.show', $package);
@endphp
{{-- Package card. Phone: horizontal (image left, details right). sm and up: vertical (image on top). Real data only. --}}
<article class="group flex h-full flex-row overflow-hidden rounded-2xl bg-brand-900 text-white shadow-card sm:flex-col">
    <a href="{{ $url }}" class="relative block w-[42%] shrink-0 overflow-hidden sm:w-full" tabindex="-1" aria-hidden="true">
        @if ($cover)
            <x-picture :media="$cover" :alt="$cover->alt" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105 sm:aspect-[3/2] sm:h-auto" sizes="(min-width:1024px) 33vw, (min-width:640px) 50vw, 42vw" />
        @else
            <x-picture :src="$fallback" :alt="$package->tourType?->image_alt ?? ''" :width="960" :height="640" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105 sm:aspect-[3/2] sm:h-auto" />
        @endif
        @if ($badge)
            <span class="label-caps absolute left-0 top-3 max-w-[88%] bg-accent px-2.5 py-1.5 text-[0.6rem] leading-tight text-ink sm:left-auto sm:right-0 sm:top-4 sm:max-w-none sm:whitespace-nowrap sm:px-4 sm:text-xs">{{ $badge }}</span>
        @endif
    </a>

    <div class="flex flex-1 flex-col p-4 sm:p-6">
        <p class="label-caps text-[0.68rem] text-accent sm:text-xs">{{ $package->nights }} night tour @if ($package->tourType)<span class="hidden text-white/50 sm:inline">&nbsp;&middot;&nbsp;</span><span class="block sm:inline">{{ $package->tourType->name }}</span>@endif</p>
        <h3 class="mt-2 text-lg font-medium leading-snug text-white sm:text-2xl"><a href="{{ $url }}" class="hover:text-accent">{{ $package->title }}</a></h3>
        <p class="mt-3 hidden line-clamp-3 text-sm leading-relaxed text-white/80 sm:block">{{ $package->summary }}</p>

        <div class="mt-4 flex items-end justify-between gap-3 sm:mt-5 sm:border-t sm:border-white/20 sm:pt-5">
            <div>
                <p class="label-caps text-[0.65rem] text-white/60">From</p>
                @if ($hasPrice)
                    <p class="font-display text-2xl font-medium text-accent sm:text-3xl">{{ $package->priceLabel() }}<span class="ml-1 text-xs font-semibold text-accent/80">pp</span></p>
                @else
                    <p class="text-sm font-semibold text-accent">{{ $package->priceLabel() }}</p>
                @endif
            </div>
            @if ($package->destination)
                <span class="hidden rounded-md bg-white/10 px-3 py-1.5 text-xs font-semibold text-white/90 sm:inline-block">{{ $package->destination->name }}</span>
            @endif
        </div>

        <div class="mt-auto flex flex-col gap-3 pt-5 sm:flex-row-reverse sm:items-center sm:justify-between sm:pt-6">
            <a href="{{ $url }}" class="label-caps text-xs text-white underline underline-offset-4 hover:text-accent">More info<span class="sr-only"> about {{ $package->title }}</span></a>
            <button type="button" class="btn btn-primary w-full sm:w-auto" @click="$store.modal.show({ id: {{ $package->id }}, name: @js($package->title) })">Get a Quote</button>
        </div>
    </div>
</article>
