@props(['src' => null, 'media' => null, 'alt' => '', 'width' => 960, 'height' => 640, 'lazy' => true, 'sizes' => '100vw', 'priority' => false])
{{--
    Responsive image. Pass either a static path under public/ (src) or a Media model (media).
    Always outputs width/height to prevent layout shift. priority=true for the hero image.
    Static images: if a downsized "name-800.webp" sits beside "name.webp" it is offered as an 800w srcset candidate,
    so phones do not download the 2000px original.
--}}
@php
    $srcset = null;
    if ($src && ! $media) {
        $small = preg_replace('/\.webp$/', '-800.webp', $src);
        if ($small !== $src && is_file(public_path($small)) && is_file(public_path($src))) {
            $srcset = asset($small).' 800w, '.asset($src).' '.(getimagesize(public_path($src))[0] ?? $width).'w';
        }
    }
@endphp
@if ($media)
    <img src="{{ $media->url() }}" srcset="{{ $media->srcset() }}" sizes="{{ $sizes }}" width="{{ $media->width }}" height="{{ $media->height }}" alt="{{ $alt ?: $media->alt }}" @if ($priority) fetchpriority="high" @else loading="{{ $lazy ? 'lazy' : 'eager' }}" decoding="async" @endif {{ $attributes }}>
@elseif ($src)
    <img src="{{ asset($src) }}" @if ($srcset) srcset="{{ $srcset }}" sizes="{{ $sizes }}" @endif width="{{ $width }}" height="{{ $height }}" alt="{{ $alt }}" @if ($priority) fetchpriority="high" @else loading="{{ $lazy ? 'lazy' : 'eager' }}" decoding="async" @endif {{ $attributes }}>
@endif
