@php
    use App\Support\NetherlandsMap as NM;
    $dests = \App\Models\Destination::active()->orderBy('sort_order')->get()->keyBy('slug');
    $stops = collect(NM::STOPS)->filter(fn ($s) => $dests->has($s[3]));
    $regions = $dests->filter(fn ($dest) => $stops->contains(fn ($s) => $s[3] === $dest->slug));
@endphp
@if ($stops->isNotEmpty())
<section class="{{ ($d['background'] ?? 'white') === 'sand' ? 'bg-sand' : 'bg-white' }} py-16 sm:py-24" aria-labelledby="rm-{{ $sid }}" x-data="{ active: null }">
    <div class="container-site grid items-center gap-10 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.05fr)] lg:gap-16">
        <div data-reveal="left">
            @if ($d['eyebrow'])<p class="label-caps text-rust">{{ $d['eyebrow'] }}</p>@endif
            <h2 id="rm-{{ $sid }}" class="mt-3 text-3xl font-medium sm:text-4xl lg:text-5xl">{!! emphasize($d['heading'], 'text-rust') !!}</h2>
            @if ($d['text'])<p class="mt-4 max-w-xl text-base leading-relaxed text-ink/75 sm:text-lg">{{ $d['text'] }}</p>@endif
            {{-- Region list: the same links as the dots on the map; hovering one lights up its dots. --}}
            <ul class="mt-6 divide-y divide-sand-200 border-y border-sand-200">
                @foreach ($regions as $dest)
                    <li>
                        <a href="{{ route('destinations.show', $dest) }}" class="group flex min-h-[3.25rem] items-center justify-between gap-4 py-3 text-ink hover:text-rust"
                           @mouseenter="active = '{{ $dest->slug }}'" @mouseleave="active = null" @focus="active = '{{ $dest->slug }}'" @blur="active = null">
                            <span><span class="font-display text-xl font-medium">{{ $dest->name }}</span><span class="mt-0.5 block text-sm text-ink/65">{{ $stops->filter(fn ($s) => $s[3] === $dest->slug)->pluck(0)->implode(' · ') }}</span></span>
                            <x-icon name="arrow" class="h-5 w-5 shrink-0 text-rust transition-transform group-hover:translate-x-1" />
                        </a>
                    </li>
                @endforeach
            </ul>
            @if ($d['button_label'])<p class="mt-7"><x-button @click="$store.modal.show()">{{ $d['button_label'] }}</x-button></p>@endif
        </div>

        <div class="relative mx-auto w-full max-w-xl" data-reveal="right">
            <div class="relative overflow-hidden rounded-3xl bg-brand-900 shadow-card" style="background-image:radial-gradient(ellipse at 50% 40%, rgb(255 255 255 / .07), transparent 65%)">
                <svg viewBox="0 0 {{ NM::WIDTH }} {{ NM::HEIGHT }}" class="block h-auto w-full" role="group" aria-label="Map of the Netherlands with the main stops">
                    <path d="{{ NM::landPath() }}" fill="rgb(255 255 255 / .09)" fill-rule="evenodd" stroke="#F2B743" stroke-opacity=".75" stroke-width="1.6" stroke-dasharray="1.5 4.5" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="{{ NM::routePath() }}" fill="none" stroke="#fff" stroke-opacity=".55" stroke-width="1.5" stroke-dasharray="6 6" stroke-linecap="round" stroke-linejoin="round" />
                    @foreach ($stops as $key => [$label, $lon, $lat, $slug, $side])
                        @php
                            [$x, $y] = NM::xy($lon, $lat);
                            $anchor = $side === 'l' ? 'end' : 'start';
                            $tx = $side === 'l' ? $x - 13 : $x + 13;
                        @endphp
                        <a href="{{ route('destinations.show', $dests[$slug]) }}" aria-label="{{ $label }}, {{ $dests[$slug]->name }}" class="group/stop outline-none"
                           @mouseenter="active = '{{ $slug }}'" @mouseleave="active = null" @focus="active = '{{ $slug }}'" @blur="active = null">
                            <circle cx="{{ $x }}" cy="{{ $y }}" r="18" fill="transparent" />
                            <circle cx="{{ $x }}" cy="{{ $y }}" r="11" fill="#F2B743" class="transition-opacity" :class="active === '{{ $slug }}' ? 'opacity-35' : 'opacity-0 group-hover/stop:opacity-35 group-focus/stop:opacity-35'" />
                            <circle cx="{{ $x }}" cy="{{ $y }}" r="5.5" fill="#F2B743" stroke="#1F4660" stroke-width="2" />
                            <text x="{{ $tx }}" y="{{ $y + 4 }}" text-anchor="{{ $anchor }}" font-size="12.5" font-weight="700" letter-spacing=".06em" fill="#fff" paint-order="stroke" stroke="#1F4660" stroke-width="3" stroke-linejoin="round" style="text-transform:uppercase;font-family:'Manrope Variable',system-ui,sans-serif">{{ $label }}</text>
                        </a>
                    @endforeach
                </svg>
            </div>
            @if ($d['tag'])
                <p class="absolute -bottom-5 left-4 flex items-center gap-3 rounded-full bg-white py-2 pl-2 pr-5 text-sm font-bold uppercase tracking-wider text-brand-900 shadow-card sm:left-6">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-accent text-ink"><x-icon name="map" class="h-4 w-4" /></span>{{ $d['tag'] }}
                </p>
            @endif
        </div>
    </div>
</section>
@endif
