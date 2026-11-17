<section class="bg-brand-900 py-16 text-white sm:py-24" aria-labelledby="wb-{{ $sid }}">
    <div class="container-site grid items-center gap-10 {{ $d['image'] ? 'lg:grid-cols-[1.2fr_0.8fr]' : '' }}">
        <div>
            <h2 id="wb-{{ $sid }}" class="text-3xl font-medium sm:text-5xl">{!! emphasize($d['heading'], 'text-accent') !!}</h2>
            <ul class="mt-8 space-y-6">
                @foreach ($d['items'] as $r)
                    <li>
                        <h3 class="flex items-center gap-2 font-sans text-lg font-bold"><x-icon name="check" class="h-5 w-5 text-accent" /> {{ $r['title'] }}</h3>
                        <p class="mt-1 text-sm leading-relaxed text-white/85">{{ $r['text'] }}</p>
                    </li>
                @endforeach
            </ul>
        </div>
        @if ($d['image'])<x-picture :src="$d['image']" :alt="$d['image_alt']" :width="900" :height="1100" class="hidden aspect-[4/5] w-full rounded-2xl object-cover shadow-card lg:block" />@endif
    </div>
</section>
