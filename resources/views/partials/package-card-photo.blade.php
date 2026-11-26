@php
    $cover = $package->images->first();
    $type = $package->tourType;
    $url = route('packages.show', $package);
    $nights = $package->nights.' '.\Illuminate\Support\Str::plural('night', $package->nights);
@endphp
{{-- Large photo card (option "Large photos with the text on top" of a package grid): the photo fills the card and the destination,
     title, nights, price and buttons sit on a dark fade at the bottom. --}}
<article class="group relative isolate flex h-full min-h-[27rem] flex-col justify-end overflow-hidden rounded-2xl bg-brand-900 text-white shadow-card">
    @if ($cover)
        <x-picture :media="$cover" :alt="$cover->alt" class="absolute inset-0 -z-20 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" sizes="(min-width:1024px) 33vw, (min-width:640px) 50vw, 88vw" />
    @else
        <x-picture :src="$type?->image ?: 'images/tour-self-drive.webp'" :alt="$type?->image_alt ?? ''" :width="960" :height="640" class="absolute inset-0 -z-20 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" sizes="(min-width:1024px) 33vw, (min-width:640px) 50vw, 88vw" />
    @endif
    <span class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-t from-ink/95 via-ink/50 via-40% to-transparent to-75%" aria-hidden="true"></span>
    @if ($type)<span class="absolute left-0 top-4 bg-brand-900 px-3 py-1.5 text-[0.7rem] font-bold uppercase tracking-[0.14em] text-white shadow-sm">{{ $type->name }}</span>@endif
    <div class="p-5 pt-24 sm:p-6 sm:pt-24">
        @if ($package->destination)<p class="label-caps text-xs font-bold text-accent">{{ $package->destination->name }}</p>@endif
        <h{{ $headingLevel ?? 3 }} class="mt-2 text-2xl font-medium leading-snug"><a href="{{ $url }}" class="hover:text-accent">{{ $package->title }}</a></h{{ $headingLevel ?? 3 }}>
        <p class="mt-2 text-sm font-semibold text-white/90">{{ $nights }} &middot; @if ($package->price_from){{ text('card_from_label') ?: 'From' }} {{ $package->priceLabel() }} {{ text('card_per_person') }}@else{{ $package->priceLabel() }}@endif</p>
        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-white/25 pt-4">
            <a href="{{ $url }}" class="inline-flex min-h-[2.75rem] items-center gap-1.5 text-sm font-bold text-white hover:text-accent">
                <span class="border-b-2 border-accent pb-0.5">{{ text('card_details_label') ?: 'Details' }}<span class="sr-only"> about {{ $package->title }}</span></span>
                <x-icon name="arrow" class="h-4 w-4" />
            </a>
            <button type="button" class="btn btn-primary shrink-0 !px-5" @click="$store.modal.show({ id: {{ $package->id }}, name: @js($package->title) })">{{ text('card_quote_label') ?: 'Get a quote' }}</button>
        </div>
    </div>
</article>
