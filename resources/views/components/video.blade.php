@props(['name', 'poster' => null, 'alt' => '', 'background' => true, 'width' => 1600, 'height' => 900])
{{--
    Placeholder-friendly video slot. Drop public/videos/{name}.mp4 (and optionally {name}.webm) to replace the poster image.
    background=true: muted loop, no controls (hero). The file is NOT downloaded with the page: the poster shows first and
    initBgVideo() in app.js loads it after page load on tablets/desktops only (see there for the conditions).
    background=false: click-to-play with controls, preload="none" so nothing downloads until played.
    Without a file it renders the poster image only.
--}}
@php
    $mp4 = public_path("videos/{$name}.mp4");
    $webm = public_path("videos/{$name}.webm");
    $has = is_file($mp4) || is_file($webm);
    $srcAttr = $background ? 'data-src' : 'src';
@endphp
@if ($has)
    <video {{ $attributes }} @if ($background) muted loop playsinline aria-hidden="true" preload="none" data-bg-video @else controls preload="none" @endif @if ($poster) poster="{{ asset($poster) }}" @endif>
        @if (is_file($webm)){!! '<source '.$srcAttr.'="'.e(asset("videos/{$name}.webm")).'" type="video/webm">' !!}@endif
        @if (is_file($mp4)){!! '<source '.$srcAttr.'="'.e(asset("videos/{$name}.mp4")).'" type="video/mp4">' !!}@endif
    </video>
@elseif ($poster)
    <x-picture :src="$poster" :alt="$alt" :width="$width" :height="$height" {{ $attributes }} />
@endif
