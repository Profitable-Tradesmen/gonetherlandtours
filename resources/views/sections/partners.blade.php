@if (count($d['items']))
@php $logos = collect($d['items'])->filter(fn ($it) => ! empty($it['image'])); @endphp
@if ($logos->isNotEmpty())
{{-- Dark brown band (so it differs from the navy sections around it): label, heading and a short text on the left, a thin divider, and a row of (white or transparent) partner logos on the right. --}}
<section class="bg-brown py-14 text-white sm:py-20" aria-labelledby="pt-{{ $sid }}">
    <div class="container-site grid items-center gap-10 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.25fr)] lg:gap-0">
        <div class="max-w-xl lg:col-start-1 lg:row-start-1 lg:pr-12" data-reveal="left">
            @if ($d['eyebrow'])
                <p class="label-caps text-white">{{ $d['eyebrow'] }}</p>
                <span class="mb-4 mt-2 flex gap-1.5" aria-hidden="true"><i class="h-[3px] w-7 bg-white/70"></i><i class="h-[3px] w-7 bg-accent"></i><i class="h-[3px] w-7 bg-white/70"></i></span>
            @endif
            <h2 id="pt-{{ $sid }}" class="text-3xl font-medium sm:text-4xl lg:text-5xl">{!! emphasize($d['heading'], 'text-accent') !!}</h2>
            @if ($d['text'])<p class="mt-5 text-base leading-relaxed text-white/90 sm:text-lg">{{ $d['text'] }}</p>@endif
        </div>
        {{-- The logos slide slowly from right to left in a loop (a marquee). The list is repeated until one group is wider than the band, then the
             group is shown twice and the track moves by exactly one group, so the loop has no jump. Pauses on hover and focus; with
             "reduce motion" the logos simply sit still, wrapped, one group only. --}}
        @php
            $group = collect();
            while ($group->count() < 8) { $group = $group->concat($logos->values()); }
            $speed = max(24, $group->count() * 5);   // seconds for one full group
        @endphp
        {{-- The logos live in a cell of their own (the right column). The sliding row is positioned INSIDE that cell and clipped to it, so it
             cannot grow the layout or draw over the text column, whatever the screen width or zoom. --}}
        <div class="relative h-24 min-w-0 overflow-hidden lg:col-start-2 lg:row-start-1 motion-reduce:h-auto" data-reveal="right">
            <div class="marquee absolute inset-y-0 left-0 right-0 overflow-hidden lg:left-6 motion-reduce:static" role="group" aria-label="{{ $d['eyebrow'] ?: 'Partners' }}">
            <div class="marquee-track" style="--marquee-time: {{ $speed }}s">
                @foreach ([0, 1] as $copy)
                    <ul class="marquee-group" @if ($copy === 1) aria-hidden="true" inert @endif>
                        @foreach ($group as $it)
                            @php
                                $url = trim((string) ($it['url'] ?? ''));
                                $size = @getimagesize(public_path($it['image'])) ?: [0, 0];
                                $img = '<img src="'.e(asset($it['image'])).'" alt="'.($copy === 1 || $loop->index >= $logos->count() ? '' : e($it['image_alt'] ?? '')).'"'.($size[0] ? ' width="'.$size[0].'" height="'.$size[1].'"' : '').' loading="lazy" decoding="async" class="h-10 w-auto max-w-[11rem] object-contain opacity-90 transition-opacity hover:opacity-100 sm:h-12 sm:max-w-[13rem]">';
                            @endphp
                            @php $repeat = $loop->index >= $logos->count(); @endphp
                            <li class="flex shrink-0 items-center {{ $repeat ? 'marquee-repeat' : '' }}" @if ($repeat) aria-hidden="true" @endif>
                                @if ($url !== '')<a href="{{ str_starts_with($url, '/') ? url($url) : $url }}" @if (! str_starts_with($url, '/')) target="_blank" rel="noopener" @endif @if ($repeat) tabindex="-1" @endif class="inline-flex min-h-[2.75rem] items-center">{!! $img !!}</a>@else{!! $img !!}@endif
                            </li>
                        @endforeach
                    </ul>
                @endforeach
            </div>
            </div>
        </div>
    </div>
</section>
@endif
@endif
