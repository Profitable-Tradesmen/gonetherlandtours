@php
    $cover = $package->images->first();
    $fallback = $package->tourType?->image ?: 'images/tour-self-drive.webp';
    $url = route('packages.show', $package);
    $meta = collect([$package->nights.' '.\Illuminate\Support\Str::plural('night', $package->nights), $package->destination?->name])->filter()->implode(' · ');
@endphp
{{-- Photo-led package card: a square, full-bleed photo with the title, a short line, a small uppercase note and one outlined quote button on it. --}}
<article class="group relative isolate flex min-h-[24rem] flex-col justify-end overflow-hidden text-white sm:min-h-[27rem]">
    @if ($cover)
        <x-picture :media="$cover" :alt="$cover->alt" class="absolute inset-0 -z-20 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" sizes="(min-width:1024px) 33vw, (min-width:768px) 50vw, 100vw" />
    @else
        <x-picture :src="$fallback" :alt="$package->tourType?->image_alt ?? ''" :width="960" :height="640" class="absolute inset-0 -z-20 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" sizes="(min-width:1024px) 33vw, (min-width:768px) 50vw, 100vw" />
    @endif
    <span class="absolute inset-0 -z-10 bg-gradient-to-t from-ink/90 via-ink/35 to-transparent"></span>
    @if ($package->is_sample)<span class="label-caps absolute right-0 top-4 bg-accent px-3 py-1.5 text-[0.65rem] leading-tight text-ink">Sample</span>@endif
    <div class="p-6 sm:p-8">
        <h4 class="font-display text-2xl font-medium leading-snug"><a href="{{ $url }}" class="after:absolute after:inset-0 hover:text-accent">{{ $package->title }}</a></h4>
        <p class="mt-1 line-clamp-2 text-sm text-white">{{ $package->summary }}</p>
        <p class="label-caps mt-4 text-[0.7rem] text-white/85">{{ $meta }}</p>
        <p class="mt-2 text-sm"><span class="label-caps text-[0.65rem] text-white/70">From</span> <span class="font-display text-lg font-medium text-accent">{{ $package->priceLabel() }}</span></p>
        <p class="relative z-10 mt-4"><button type="button" class="inline-flex min-h-[2.75rem] items-center border border-white px-8 py-2.5 text-sm font-semibold uppercase tracking-wide transition-colors hover:bg-white hover:text-ink" @click="$store.modal.show({ id: {{ $package->id }}, name: @js($package->title) })">Get a Quote</button></p>
    </div>
</article>
