{{-- Cards for towns and sights (photo, name, short note). Expects $places; optional $headingLevel (default 3). --}}
<x-slider tag="ul" class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
    @foreach ($places as $place)
        <li class="flex">
            <article class="flex w-full flex-col overflow-hidden rounded-2xl border border-ink/10 bg-white text-ink shadow-card">
                @if ($place->image)<x-picture :src="$place->image" :alt="$place->image_alt ?? ''" :width="1600" :height="1067" class="aspect-[3/2] h-auto w-full object-cover" sizes="(min-width:1280px) 25vw, (min-width:640px) 45vw, 85vw" />@endif
                <div class="flex-1 p-5">
                    <h{{ $headingLevel ?? 3 }} class="text-xl font-medium">{{ $place->name }}</h{{ $headingLevel ?? 3 }}>
                    @if ($place->note)<p class="mt-2 text-sm leading-relaxed text-ink/80">{{ $place->note }}</p>@endif
                </div>
            </article>
        </li>
    @endforeach
</x-slider>
