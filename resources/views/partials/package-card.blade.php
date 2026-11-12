@php
    $cover = $package->images->first();
    $fallback = $package->tourType?->image ?: 'images/tour-self-drive.webp';
@endphp
<article class="flex flex-col overflow-hidden rounded-2xl border border-sand-200 bg-white shadow-card">
    <a href="{{ route('packages.show', $package) }}" class="block" tabindex="-1" aria-hidden="true">
        @if ($cover)
            <x-picture :media="$cover" :alt="$cover->alt" class="aspect-[3/2] w-full object-cover" sizes="(min-width:1024px) 33vw, (min-width:640px) 50vw, 100vw" />
        @else
            <x-picture :src="$fallback" :alt="$package->tourType?->image_alt ?? ''" :width="960" :height="640" class="aspect-[3/2] w-full object-cover" />
        @endif
    </a>
    <div class="flex flex-1 flex-col p-5">
        <p class="label-caps text-rust">{{ $package->tourType?->name }} &middot; {{ $package->nights }} nights</p>
        <h3 class="mt-2 text-xl font-medium"><a href="{{ route('packages.show', $package) }}" class="hover:text-rust">{{ $package->title }}</a></h3>
        <p class="mt-2 flex-1 text-sm text-ink/75">{{ $package->summary }}</p>
        <p class="mt-4 text-sm">From <strong class="font-display text-2xl">{{ $package->priceLabel() }}</strong> <span class="text-ink/60">per person</span></p>
        <div class="mt-4 grid grid-cols-2 gap-2">
            <x-button variant="secondary" :href="route('packages.show', $package)" class="!px-3">Details</x-button>
            <x-button variant="primary" :href="route('packages.show', $package).'#quote'" class="!px-3">Get a Quote</x-button>
        </div>
    </div>
</article>
