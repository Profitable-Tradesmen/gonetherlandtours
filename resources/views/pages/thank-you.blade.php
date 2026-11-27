@extends('layouts.app')

@section('content')
<section class="container-site py-20 text-center">
    <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-accent text-ink"><x-icon name="check" class="h-8 w-8" /></span>
    <h1 class="mt-6 text-4xl font-medium sm:text-5xl">{{ text('thankyou_heading') }}{{ $name ? ', '.$name : '' }}!</h1>
    <p class="mx-auto mt-4 max-w-xl text-lg text-ink/80">{{ text('thankyou_text') }}</p>
    <p class="mt-8"><x-button :href="route('packages.index')" variant="secondary">{{ text('thankyou_button') }}</x-button></p>
</section>
@include('partials.explore-cards', ['title' => 'While you *wait*'])
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
