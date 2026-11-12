@extends('layouts.app')

@section('content')
<section class="container-site py-20 text-center">
    <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-emerald-800"><x-icon name="check" class="h-8 w-8" /></span>
    <h1 class="mt-6 text-4xl font-medium sm:text-5xl">Thank you{{ $name ? ', '.$name : '' }}!</h1>
    <p class="mx-auto mt-4 max-w-xl text-lg text-ink/80">We have received your enquiry. A Netherlands travel specialist will be in touch soon with your tailored quote.</p>
    <p class="mt-8"><x-button :href="route('packages.index')" variant="secondary">Browse tour packages</x-button></p>
</section>
@endsection

@if ($enquiryId)
@push('scripts')
<script>
    // Conversion event for Google Tag Manager. Attach the Google Ads conversion to this event in GTM.
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({
        event: 'enquiry_thank_you',
        form_name: @js($formName),
        enquiry_id: @js($enquiryId)
    });
</script>
@endpush
@endif
