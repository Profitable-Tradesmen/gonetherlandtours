@if (count($d['items']))
@php $logos = collect($d['items'])->filter(fn ($it) => ! empty($it['image'])); @endphp
@if ($logos->isNotEmpty())
{{-- Dark brown band (so it differs from the navy sections around it): label, heading and a short text on the left, a thin divider, and a row of (white or transparent) partner logos on the right. --}}
<section class="bg-brown py-14 text-white sm:py-20" aria-labelledby="pt-{{ $sid }}">
    <div class="container-site grid items-center gap-10 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.25fr)] lg:gap-0">
        <div class="max-w-xl lg:pr-12" data-reveal="left">
            @if ($d['eyebrow'])
                <p class="label-caps text-white">{{ $d['eyebrow'] }}</p>
                <span class="mb-4 mt-2 flex gap-1.5" aria-hidden="true"><i class="h-[3px] w-7 bg-white/70"></i><i class="h-[3px] w-7 bg-accent"></i><i class="h-[3px] w-7 bg-white/70"></i></span>
            @endif
            <h2 id="pt-{{ $sid }}" class="text-3xl font-medium sm:text-4xl lg:text-5xl">{!! emphasize($d['heading'], 'text-accent') !!}</h2>
            @if ($d['text'])<p class="mt-5 text-base leading-relaxed text-white/90 sm:text-lg">{{ $d['text'] }}</p>@endif
        </div>
        <ul class="flex flex-wrap items-center justify-center gap-x-10 gap-y-8 lg:border-l lg:border-white/25 lg:pl-12" data-reveal="right">
            @foreach ($logos as $it)
                @php
                    $url = trim((string) ($it['url'] ?? ''));
                    $size = @getimagesize(public_path($it['image'])) ?: [0, 0];
                    $img = '<img src="'.e(asset($it['image'])).'" alt="'.e($it['image_alt'] ?? '').'"'.($size[0] ? ' width="'.$size[0].'" height="'.$size[1].'"' : '').' loading="lazy" decoding="async" class="h-10 w-auto max-w-[11rem] object-contain opacity-90 transition-opacity hover:opacity-100 sm:h-12 sm:max-w-[13rem]">';
                @endphp
                <li class="flex items-center">
                    @if ($url !== '')<a href="{{ str_starts_with($url, '/') ? url($url) : $url }}" @if (! str_starts_with($url, '/')) target="_blank" rel="noopener" @endif class="inline-flex min-h-[2.75rem] items-center">{!! $img !!}</a>@else{!! $img !!}@endif
                </li>
            @endforeach
        </ul>
    </div>
</section>
@endif
@endif
