{{-- Site logo: public/images/GoNetherlandTours-trim.webp (2146x733). A Admin > Settings upload overrides it.
     Mobile (<640px): public/images/GoNetherlandTours-mobile.webp is used if it exists, otherwise the main logo at a smaller size.
     On the dark footer ($light) the logo sits on a white rounded plate. --}}
@php
    $brand = setting('brand_name', 'GoNetherlandTours');
    $mobile = is_file(public_path('images/GoNetherlandTours-mobile.webp')) ? 'images/GoNetherlandTours-mobile.webp' : null;
@endphp
<span class="inline-block {{ ($light ?? false) ? 'rounded-xl bg-white px-3 py-2' : '' }}">
    @if (setting('logo'))
        <img src="{{ asset(setting('logo')) }}" alt="{{ $brand }}" height="40" class="h-9 w-auto sm:h-10">
    @else
        <picture>
            @if ($mobile)<source media="(max-width: 639px)" srcset="{{ asset($mobile) }}">@endif
            <img src="{{ asset(is_file(public_path('images/GoNetherlandTours-trim-600.webp')) ? 'images/GoNetherlandTours-trim-600.webp' : 'images/GoNetherlandTours-trim.webp') }}" width="2067" height="525" alt="{{ $brand }}" class="h-8 w-auto sm:h-10 lg:h-[4.375rem]">
        </picture>
    @endif
</span>
