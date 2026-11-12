{{-- Text wordmark placeholder. Upload a logo in Admin > Settings to replace it. --}}
@if (setting('logo'))
    <img src="{{ asset(setting('logo')) }}" alt="{{ setting('brand_name', 'GoNetherlandTours') }}" height="40" class="h-10 w-auto">
@else
    <span class="font-display text-2xl leading-none tracking-tight {{ $light ?? false ? 'text-white' : 'text-brand-700' }}">
        <span class="italic font-medium">go</span><span class="font-semibold">Netherland</span><span class="italic font-medium text-rust">tours</span>
    </span>
@endif
