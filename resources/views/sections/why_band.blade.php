@php
    $bg = (string) ($d['bg'] ?? '');
    $hasSide = ! empty($d['image']);
@endphp
{{-- Dark "why us" band. The background is a photo, an animated GIF or a short video chosen in the admin (a dark tint keeps the text readable);
     with no background it is plain navy. A video loads after the page and plays on tablets and desktops only (initBgVideo in app.js);
     phones, Data Saver and "reduce motion" visitors see the fallback photo instead. --}}
<section class="relative isolate overflow-hidden bg-brand-900 py-16 text-white sm:py-24" aria-labelledby="wb-{{ $sid }}">
    @include('partials.section-bg', ['bg' => $bg, 'poster' => $d['poster'] ?? '', 'posterAlt' => $d['poster_alt'] ?? '', 'tint' => $d['overlay'] ?? 'medium'])
    <div class="container-site">
        {{-- Heading on the left, a short intro and the quote button on the right (below on phones). --}}
        <div class="flex flex-wrap items-end justify-between gap-x-10 gap-y-5">
            <div class="max-w-3xl" data-reveal="up">
                @if ($d['eyebrow'])<p class="label-caps inline-flex items-center gap-3 text-accent"><span class="h-px w-7 bg-accent" aria-hidden="true"></span><span class="h-2 w-2 rounded-full bg-rust ring-2 ring-white/50" aria-hidden="true"></span>{{ $d['eyebrow'] }}</p>@endif
                <h2 id="wb-{{ $sid }}" class="mt-3 drop-shadow-md text-3xl font-medium sm:text-4xl lg:text-5xl">{!! emphasize($d['heading'], 'text-accent') !!}</h2>
                @if ($d['intro'])<p class="mt-4 max-w-2xl text-base leading-relaxed text-white drop-shadow-md sm:text-lg">{{ $d['intro'] }}</p>@endif
            </div>
            @if ($d['button_label'])<div data-reveal="up"><x-button @click="$store.modal.show()">{{ $d['button_label'] }}</x-button></div>@endif
        </div>

        {{-- Reason tiles. With a side photo: photo + a 2 x 2 grid. Without one: all tiles in one row on desktop (two columns on tablets, stacked on phones). --}}
        <div class="mt-10 grid gap-5 {{ $hasSide ? 'lg:grid-cols-[0.9fr_1.1fr]' : '' }}">
            @if ($hasSide)<x-picture :src="$d['image']" :alt="$d['image_alt']" :width="900" :height="1100" class="hidden h-full min-h-[22rem] w-full rounded-2xl object-cover shadow-card lg:block" />@endif
            <ul class="grid gap-5 sm:grid-cols-2 {{ $hasSide ? 'sm:grid-rows-2' : 'lg:grid-cols-4' }}">
                @foreach ($d['items'] as $i => $r)
                    <li class="group relative flex flex-col items-center justify-center overflow-hidden rounded-2xl p-6 text-center ring-1 ring-white/15 transition duration-300 hover:-translate-y-0.5 sm:p-8 {{ $bg !== '' ? 'bg-brand-900/80 backdrop-blur-sm ring-white/25 hover:bg-brand-900/90' : 'bg-white/10 hover:bg-white/15' }}" data-reveal="up" style="--d:{{ $i * 100 }}ms">
                        @if ($d['show_numbers'] ?? true)<span class="absolute right-4 top-3 font-display text-4xl font-medium text-white/40" aria-hidden="true">{{ sprintf('%02d', $i + 1) }}</span>@endif
                        <span class="flex h-16 w-16 items-center justify-center rounded-full bg-accent text-ink shadow-card transition-transform duration-300 group-hover:scale-105"><x-icon :name="($r['icon'] ?? '') ?: 'check'" class="h-7 w-7" /></span>
                        <h3 class="mt-5 font-display text-xl font-medium sm:text-2xl">{{ $r['title'] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-white/90 sm:text-base">{{ $r['text'] }}</p>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</section>
