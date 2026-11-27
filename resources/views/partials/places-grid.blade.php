{{-- Cards for towns and sights (photo, name, short note). Expects $places; optional $headingLevel (default 3). --}}
<x-slider tag="ul" class="flex flex-wrap justify-center gap-5">
    @foreach ($places as $place)
        <li class="flex sm:w-[calc(50%-0.625rem)] lg:w-[calc(33.333%-0.84rem)] {{ in_array($places->count(), [5, 6, 9], true) ? '' : 'xl:w-[calc(25%-0.94rem)]' }}">
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
