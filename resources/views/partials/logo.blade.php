{{-- Site logo. Default: public/images/logo.webp (+ logo-2x.webp). Upload a logo in Admin > Settings to override.
     The logo artwork is navy on white, so on the dark footer ($light) it sits on a white rounded plate. --}}
@php $brand = setting('brand_name', 'GoNetherlandTours'); @endphp
<span class="inline-block {{ ($light ?? false) ? 'rounded-xl bg-white px-3 py-2' : '' }}">
    @if (setting('logo'))
        <img src="{{ asset(setting('logo')) }}" alt="{{ $brand }}" height="40" class="h-9 w-auto sm:h-10">
    @else
        <img src="{{ asset('images/logo.webp') }}" srcset="{{ asset('images/logo.webp') }} 900w, {{ asset('images/logo-2x.webp') }} 1500w"
             sizes="(min-width:640px) 266px, 240px" width="900" height="135" alt="{{ $brand }}" class="h-9 w-auto sm:h-10">
    @endif
</span>
