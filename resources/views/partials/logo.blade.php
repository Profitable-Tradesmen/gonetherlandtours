{{-- Site logo (windmill seal, 2026 design): public/images/logo-gonetherland.svg (full color) and logo-gonetherland-dark.svg (for dark backgrounds).
     Header, base state (transparent over a photo hero): the dark-background logo; sticky/solid state: the full-color logo. Footer ($light): the dark-background logo.
     The swap is CSS only (see .hdr-logo in app.css). A logo uploaded in Admin > Settings overrides both and is shown as is. The browser icons use the round symbol. --}}
@php $brand = setting('brand_name', 'GoNetherlandTours'); $size = 'h-9 w-auto sm:h-11 lg:h-14 2xl:h-16'; @endphp
<span class="inline-block">
    @if (setting('logo'))
        <img src="{{ asset(setting('logo')) }}" alt="{{ $brand }}" height="40" class="h-9 w-auto sm:h-10">
    @elseif ($light ?? false)
        <img src="{{ asset_v('images/logo-gonetherland-dark.svg') }}" width="574" height="122" alt="{{ $brand }}" class="h-11 w-auto sm:h-12">
    @else
        <img src="{{ asset_v('images/logo-gonetherland.svg') }}" width="574" height="122" alt="{{ $brand }}" class="logo-color {{ $size }}">
        <img src="{{ asset_v('images/logo-gonetherland-dark.svg') }}" width="574" height="122" alt="" aria-hidden="true" class="logo-white {{ $size }}">
    @endif
</span>
