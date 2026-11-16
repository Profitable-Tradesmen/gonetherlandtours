@props(['name', 'poster' => null, 'alt' => '', 'background' => true, 'width' => 1600, 'height' => 900])
{{--
    Placeholder-friendly video slot. Drop public/videos/{name}.mp4 (and optionally {name}.webm) to replace the poster image.
    background=true: muted autoplay loop, no controls (hero). background=false: click-to-play with controls.
    Without a file it renders the poster image only. Respects prefers-reduced-motion (JS pauses autoplay).
--}}
@php
    $mp4 = public_path("videos/{$name}.mp4");
    $webm = public_path("videos/{$name}.webm");
    $has = is_file($mp4) || is_file($webm);
@endphp
@if ($has)
    <video {{ $attributes }} @if ($background) autoplay muted loop playsinline aria-hidden="true" data-bg-video @else controls preload="metadata" @endif @if ($poster) poster="{{ asset($poster) }}" @endif>
        @if (is_file($webm))<source src="{{ asset("videos/{$name}.webm") }}" type="video/webm">@endif
        @if (is_file($mp4))<source src="{{ asset("videos/{$name}.mp4") }}" type="video/mp4">@endif
    </video>
@elseif ($poster)
    <x-picture :src="$poster" :alt="$alt" :width="$width" :height="$height" {{ $attributes }} />
@endif
