@php
    $cover = $package->images->first();
    $fallback = $package->tourType?->image ?: 'images/tour-self-drive.webp';
    $hasPrice = (bool) $package->price_from;
    $url = route('packages.show', $package);
    $nights = $package->nights.' '.\Illuminate\Support\Str::plural('night', $package->nights);
    $type = $package->tourType;
    $dest = $package->destination?->name;
    $badge = $package->is_sample ? (text('card_sample_badge') ?: null) : null;
@endphp
{{-- Home page package card (used by sections/packages_tabs). Same look on every screen size, deliberately sparse:
     photo with a navy tour-type tab and the number of nights along the bottom, the destination, title, a two-line summary,
     a "details" link, and a footer with the price on the left and one main button on the right. Used on the home page, the Packages page,
     destination and tour type pages and the related trips on a package page. --}}
<article class="group flex h-full flex-col overflow-hidden rounded-2xl border border-ink/10 bg-white text-ink shadow-card transition duration-300 hover:-translate-y-1 hover:shadow-[0_24px_44px_-18px_rgb(12_13_17/0.4)]">
    <a href="{{ $url }}" class="relative block overflow-hidden" tabindex="-1" aria-hidden="true">
        @if ($cover)
            <x-picture :media="$cover" :alt="$cover->alt" class="aspect-[3/2] h-auto w-full object-cover transition-transform duration-700 group-hover:scale-105" sizes="(min-width:1024px) 33vw, (min-width:640px) 50vw, 88vw" />
        @else
            <x-picture :src="$fallback" :alt="$type?->image_alt ?? ''" :width="960" :height="640" class="aspect-[3/2] h-auto w-full object-cover transition-transform duration-700 group-hover:scale-105" sizes="(min-width:1024px) 33vw, (min-width:640px) 50vw, 88vw" />
        @endif
        @if ($type)
            <span class="absolute left-0 top-4 bg-brand-900 px-3 py-1.5 text-[0.7rem] font-bold uppercase tracking-[0.14em] text-white shadow-sm">{{ $type->name }}</span>
        @endif
        @if ($badge)<span class="label-caps absolute right-0 top-4 whitespace-nowrap bg-accent px-3 py-1.5 text-xs leading-tight text-ink">{{ $badge }}</span>@endif
        <span class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-ink/70 to-transparent px-4 pb-3 pt-10 text-sm font-semibold text-white">{{ $nights }}</span>
    </a>
    <div class="relative isolate flex min-w-0 flex-1 flex-col overflow-hidden p-5 sm:p-6">
        @include('partials.place-icon-bg', ['slug' => $package->destination?->slug])
        @if ($dest)<p class="label-caps flex items-center gap-1.5 text-xs font-bold text-rust"><x-icon name="map" class="h-4 w-4" />{{ $dest }}</p>@endif
        <h{{ $headingLevel ?? 3 }} class="mt-2.5 line-clamp-2 text-[1.45rem] font-medium leading-snug"><a href="{{ $url }}" class="hover:text-rust">{{ $package->title }}</a></h{{ $headingLevel ?? 3 }}>
        @if ($stops = $package->routeStops())
            <p class="mt-3 border-y border-sand-200 py-2.5">
                <span class="label-caps block text-[0.65rem] text-ink/60">{{ text('card_route_label') ?: 'Route' }}</span>
                <span class="mt-0.5 block text-sm font-semibold leading-snug text-brand-900">@foreach ($stops as $stop)<span class="whitespace-nowrap">{{ $stop }}@if (! $loop->last)<span class="mx-1.5 text-rust" aria-hidden="true">&rsaquo;</span><span class="sr-only">, </span>@endif</span> @endforeach</span>
            </p>
        @endif
        <p class="mt-2 line-clamp-2 min-h-[3rem] text-[0.95rem] leading-relaxed text-ink/80">{{ $package->summary }}</p>
        <a href="{{ $url }}" class="group/link mt-3 inline-flex min-h-[2.75rem] items-center gap-1.5 self-start text-sm font-bold text-brand-700 hover:text-rust">
            <span class="border-b border-current pb-0.5">{{ text('card_details_label') ?: 'Details' }}<span class="sr-only"> about {{ $package->title }}</span></span>
            <x-icon name="arrow" class="h-4 w-4 transition-transform group-hover/link:translate-x-1" />
        </a>
        <div class="mt-auto flex items-end justify-between gap-3 border-t border-sand-200 pt-4">
            <div>
                <p class="label-caps text-[0.7rem] text-ink/60">{{ text('card_from_label') ?: 'From' }}</p>
                <p class="font-display text-3xl font-medium leading-none text-brand-900">{{ $package->priceLabel() }}@if ($hasPrice && text('card_per_person'))<span class="ml-1 font-sans text-xs font-semibold text-ink/60">{{ text('card_per_person') }}</span>@endif</p>
            </div>
            <button type="button" class="btn btn-primary shrink-0 !px-5" @click="$store.modal.show({ id: {{ $package->id }}, name: @js($package->title) })">{{ text('card_quote_label') ?: 'Get a quote' }}</button>
        </div>
    </div>
</article>
