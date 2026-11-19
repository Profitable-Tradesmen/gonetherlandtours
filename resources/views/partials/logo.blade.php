{{-- Site logo. New artwork (SVG): public/images/logo-gonetherland.svg (full color) and logo-gonetherland-dark.svg (for dark backgrounds: white windmill and wordmark, orange tulips and "Netherland", colored flag).
     Header, base state (transparent over a photo hero): the DARK-BACKGROUND logo (white + orange + colored flag). Header, sticky/solid state (white bar after scrolling, menus open, pages without a photo hero): the FULL-COLOR logo.
     Footer ($light, dark background): the dark-background logo. The swap is CSS only (see .hdr-logo in app.css). A logo uploaded in Admin > Settings overrides both and is shown as is. --}}
@php $brand = setting('brand_name', 'GoNetherlandTours'); $size = 'h-9 w-auto sm:h-11 lg:h-16'; @endphp
<span class="inline-block">
    @if (setting('logo'))
        <img src="{{ asset(setting('logo')) }}" alt="{{ $brand }}" height="40" class="h-9 w-auto sm:h-10">
    @elseif ($light ?? false)
        <img src="{{ asset('images/logo-gonetherland-dark.svg') }}" width="1417" height="262" alt="{{ $brand }}" class="h-11 w-auto sm:h-12">
    @else
        <img src="{{ asset('images/logo-gonetherland.svg') }}" width="1417" height="262" alt="{{ $brand }}" class="logo-color {{ $size }}">
        <img src="{{ asset('images/logo-gonetherland-dark.svg') }}" width="1417" height="262" alt="" aria-hidden="true" class="logo-white {{ $size }}">
    @endif
</span>
