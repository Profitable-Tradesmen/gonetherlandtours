<section class="bg-brand-900 py-16 text-white sm:py-24" aria-labelledby="wb-{{ $sid }}">
    <div class="container-site">
        {{-- Heading on the left, a short intro and the quote button on the right (below on phones). --}}
        <div class="flex flex-wrap items-end justify-between gap-x-10 gap-y-5">
            <div class="max-w-3xl" data-reveal="up">
                @if ($d['eyebrow'])<p class="label-caps inline-flex items-center gap-3 text-accent"><span class="h-px w-7 bg-accent" aria-hidden="true"></span><span class="h-2 w-2 rounded-full bg-rust ring-2 ring-white/50" aria-hidden="true"></span>{{ $d['eyebrow'] }}</p>@endif
                <h2 id="wb-{{ $sid }}" class="mt-3 text-3xl font-medium sm:text-4xl lg:text-5xl">{!! emphasize($d['heading'], 'text-accent') !!}</h2>
                @if ($d['intro'])<p class="mt-4 max-w-2xl text-base leading-relaxed text-white/85 sm:text-lg">{{ $d['intro'] }}</p>@endif
            </div>
            @if ($d['button_label'])<div data-reveal="up"><x-button @click="$store.modal.show()">{{ $d['button_label'] }}</x-button></div>@endif
        </div>

        {{-- Photo on one side, a 2 x 2 grid of reason tiles on the other (stacked on phones). Tile content is centered in the tile so there is no empty gap. --}}
        <div class="mt-10 grid gap-5 {{ $d['image'] ? 'lg:grid-cols-[0.9fr_1.1fr]' : '' }}">
            @if ($d['image'])<x-picture :src="$d['image']" :alt="$d['image_alt']" :width="900" :height="1100" class="hidden h-full min-h-[22rem] w-full rounded-2xl object-cover shadow-card lg:block" />@endif
            <ul class="grid gap-5 sm:grid-cols-2 sm:grid-rows-2">
                @foreach ($d['items'] as $i => $r)
                    <li class="group relative flex flex-col items-center justify-center overflow-hidden rounded-2xl bg-white/10 p-6 text-center ring-1 ring-white/15 transition duration-300 hover:-translate-y-0.5 hover:bg-white/15 sm:p-8" data-reveal="up" style="--d:{{ $i * 100 }}ms">
                        <span class="absolute right-4 top-3 font-display text-4xl font-medium text-white/10" aria-hidden="true">{{ sprintf('%02d', $i + 1) }}</span>
                        <span class="flex h-16 w-16 items-center justify-center rounded-full bg-accent text-ink shadow-card transition-transform duration-300 group-hover:scale-105"><x-icon :name="($r['icon'] ?? '') ?: 'check'" class="h-7 w-7" /></span>
                        <h3 class="mt-5 font-display text-xl font-medium sm:text-2xl">{{ $r['title'] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-white/85 sm:text-base">{{ $r['text'] }}</p>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</section>
