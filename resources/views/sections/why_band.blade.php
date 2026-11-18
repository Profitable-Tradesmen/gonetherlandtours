<section class="bg-brand-900 py-16 text-white sm:py-24" aria-labelledby="wb-{{ $sid }}">
    <div class="container-site">
        <h2 id="wb-{{ $sid }}" class="max-w-3xl text-3xl font-medium sm:text-4xl lg:text-5xl">{!! emphasize($d['heading'], 'text-accent') !!}</h2>
        {{-- Photo on one side, a 2 x 2 grid of reason tiles on the other (stacked on phones). --}}
        <div class="mt-10 grid gap-5 {{ $d['image'] ? 'lg:grid-cols-[0.9fr_1.1fr]' : '' }}">
            @if ($d['image'])<x-picture :src="$d['image']" :alt="$d['image_alt']" :width="900" :height="1100" class="hidden h-full min-h-[22rem] w-full rounded-2xl object-cover shadow-card lg:block" />@endif
            <ul class="grid gap-5 sm:grid-cols-2">
                @foreach ($d['items'] as $i => $r)
                    <li class="rounded-2xl bg-white/10 p-6 text-center ring-1 ring-white/15" data-reveal="up" style="--d:{{ $i * 100 }}ms">
                        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-brand-700 text-accent"><x-icon name="check" class="h-6 w-6" /></span>
                        <h3 class="mt-4 font-display text-xl font-medium">{{ $r['title'] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-white/85">{{ $r['text'] }}</p>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</section>
