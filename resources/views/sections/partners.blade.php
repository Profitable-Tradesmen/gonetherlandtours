@if (count($d['items']))
@php $logos = collect($d['items'])->filter(fn ($it) => ! empty($it['image']))->values(); @endphp
@if ($logos->isNotEmpty())
{{-- Navy band: label, heading and short text on the left, a thin divider, and the partner logos on the right. The logos glide to the left in one smooth,
     continuous loop (two identical groups, no stepping and no jump). Pauses on hover and focus; with "reduce motion" nothing moves and the logos simply wrap. --}}
@php
    $n = $logos->count();
    $perGroup = max(1, (int) ceil(6 / $n)) * $n;   // enough logos in one group to be wider than the box, so the loop never shows a gap
@endphp
<section class="bg-brand-900 py-14 text-white sm:py-20" aria-labelledby="pt-{{ $sid }}">
    <div class="container-site grid items-center gap-10 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.3fr)] lg:gap-0">
        <div class="max-w-xl lg:pr-12" data-reveal="left">
            @if ($d['eyebrow'])
                <p class="label-caps text-white">{{ $d['eyebrow'] }}</p>
                <span class="mb-4 mt-3 flex items-center gap-2" aria-hidden="true"><i class="h-0.5 w-9 bg-accent"></i><i class="h-1.5 w-1.5 rotate-45 bg-accent"></i><i class="h-0.5 w-9 bg-accent"></i></span>
            @endif
            <h2 id="pt-{{ $sid }}" class="text-3xl font-medium sm:text-4xl lg:text-5xl">{!! emphasize($d['heading'], 'text-accent') !!}</h2>
            @if ($d['text'])<p class="mt-5 text-base leading-relaxed text-white/90 sm:text-lg">{{ $d['text'] }}</p>@endif
        </div>
        <div class="relative min-w-0 overflow-hidden lg:border-l lg:border-white/25 lg:py-6 lg:pl-8" data-reveal="right" role="group" aria-roledescription="carousel" aria-label="{{ $d['eyebrow'] ?: 'Partners' }}">
            <div class="marquee" style="--marquee-time:{{ max(24, $perGroup * 6) }}s">
                <div class="marquee-track">
                    @foreach (range(1, 2) as $copy)
                        <ul class="marquee-group" style="gap:4rem;padding-right:4rem" @if ($copy === 2) aria-hidden="true" @endif>
                            @for ($k = 0; $k < $perGroup; $k++)
                                @php
                                    $it = $logos[$k % $n];
                                    $url = trim((string) ($it['url'] ?? ''));
                                    $size = @getimagesize(public_path($it['image'])) ?: [0, 0];
                                    $img = '<img src="'.e(asset($it['image'])).'" alt="'.(($copy === 2 || $k >= $n) ? '' : e($it['image_alt'] ?? '')).'"'.($size[0] ? ' width="'.$size[0].'" height="'.$size[1].'"' : '').' loading="lazy" decoding="async" class="h-14 w-auto max-w-none object-contain">';
                                    $hidden = $copy === 2 || $k >= $n;
                                @endphp
                                <li class="flex shrink-0 items-center" @if ($hidden) aria-hidden="true" @endif>
                                    @if ($url !== '')<a href="{{ str_starts_with($url, '/') ? url($url) : $url }}" @if (! str_starts_with($url, '/')) target="_blank" rel="noopener" @endif @if ($hidden) tabindex="-1" @endif class="inline-flex min-h-[2.75rem] items-center">{!! $img !!}</a>@else{!! $img !!}@endif
                                </li>
                            @endfor
                        </ul>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
@endif
@endif
