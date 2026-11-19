@if (count($d['items']))
@php $logos = collect($d['items'])->filter(fn ($it) => ! empty($it['image']))->values(); @endphp
@if ($logos->isNotEmpty())
{{-- Dark band: label, heading and short text on the left, a thin divider, and the partner logos on the right. The logos show 3 at a time (2 on tablets,
     1 on phones) and slide by ONE logo every few seconds, looping with no jump (the first logos are repeated at the end, then the track snaps back).
     Pauses on hover and focus; with "reduce motion" nothing moves and the logos simply wrap. --}}
@php
    $n = $logos->count();
    $items = $logos->concat($logos->pipe(fn ($l) => collect(range(0, 2))->map(fn ($k) => $l[$k % $n])));   // originals + 3 repeats
@endphp
<section class="bg-brown py-14 text-white sm:py-20" aria-labelledby="pt-{{ $sid }}">
    <div class="container-site grid items-center gap-10 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.3fr)] lg:gap-0">
        <div class="max-w-xl lg:pr-12" data-reveal="left">
            @if ($d['eyebrow'])
                <p class="label-caps text-white">{{ $d['eyebrow'] }}</p>
                <span class="mb-4 mt-2 flex gap-1.5" aria-hidden="true"><i class="h-[3px] w-7 bg-white/70"></i><i class="h-[3px] w-7 bg-accent"></i><i class="h-[3px] w-7 bg-white/70"></i></span>
            @endif
            <h2 id="pt-{{ $sid }}" class="text-3xl font-medium sm:text-4xl lg:text-5xl">{!! emphasize($d['heading'], 'text-accent') !!}</h2>
            @if ($d['text'])<p class="mt-5 text-base leading-relaxed text-white/90 sm:text-lg">{{ $d['text'] }}</p>@endif
        </div>
        <div class="min-w-0 lg:border-l lg:border-white/25 lg:py-6 lg:pl-8" data-reveal="right"
             x-data="partnerSlider({{ $n }})" @mouseenter="paused = true" @mouseleave="paused = false" @focusin="paused = true" @focusout="paused = false"
             role="group" aria-roledescription="carousel" aria-label="{{ $d['eyebrow'] ?: 'Partners' }}">
            <div class="pslider">
                <ul class="pslider-track" :style="'--i:' + i + ';--dur:' + (anim ? '1.8s' : '0s')" @transitionend.self="end()">
                    @foreach ($items as $k => $it)
                        @php
                            $clone = $k >= $n;
                            $url = trim((string) ($it['url'] ?? ''));
                            $size = @getimagesize(public_path($it['image'])) ?: [0, 0];
                            $img = '<img src="'.e(asset($it['image'])).'" alt="'.($clone ? '' : e($it['image_alt'] ?? '')).'"'.($size[0] ? ' width="'.$size[0].'" height="'.$size[1].'"' : '').' loading="lazy" decoding="async" class="h-14 w-auto max-w-full object-contain opacity-90 transition-opacity hover:opacity-100 sm:h-16 lg:h-[4.5rem]">';
                        @endphp
                        <li class="pslider-item {{ $clone ? 'pslider-clone' : '' }}" :class="shown({{ $k }}) ? 'opacity-100' : 'opacity-0'" @if ($clone) aria-hidden="true" @endif>
                            @if ($url !== '')<a href="{{ str_starts_with($url, '/') ? url($url) : $url }}" @if (! str_starts_with($url, '/')) target="_blank" rel="noopener" @endif @if ($clone) tabindex="-1" @endif class="inline-flex min-h-[2.75rem] items-center">{!! $img !!}</a>@else{!! $img !!}@endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</section>
@endif
@endif
