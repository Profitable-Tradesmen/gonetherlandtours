@props(['src' => null, 'media' => null, 'alt' => '', 'width' => 960, 'height' => 640, 'lazy' => true, 'sizes' => '100vw', 'priority' => false])
{{--
    Responsive image. Pass either a static path under public/ (src) or a Media model (media).
    Always outputs width/height to prevent layout shift. priority=true for the hero image.
--}}
@if ($media)
    <img src="{{ $media->url() }}" srcset="{{ $media->srcset() }}" sizes="{{ $sizes }}" width="{{ $media->width }}" height="{{ $media->height }}" alt="{{ $alt ?: $media->alt }}" @if ($priority) fetchpriority="high" @else loading="{{ $lazy ? 'lazy' : 'eager' }}" decoding="async" @endif {{ $attributes }}>
@elseif ($src)
    <img src="{{ asset($src) }}" width="{{ $width }}" height="{{ $height }}" alt="{{ $alt }}" @if ($priority) fetchpriority="high" @else loading="{{ $lazy ? 'lazy' : 'eager' }}" decoding="async" @endif {{ $attributes }}>
@endif
