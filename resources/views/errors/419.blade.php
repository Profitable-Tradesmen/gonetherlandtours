@extends('layouts.app', ['seo' => \App\Support\Seo::make(['title' => 'Page expired | '.setting('brand_name', 'GoNetherlandTours'), 'noindex' => true])])

@section('content')
<section class="container-site py-24 text-center">
    <p class="label-caps text-rust">Page expired</p>
    <h1 class="mt-3 text-4xl font-medium sm:text-5xl">That form timed out</h1>
    <p class="mx-auto mt-4 max-w-md text-ink/75">For your security the page expires if it stays open for a long time. Please go back, refresh the page and send it again. Nothing was submitted.</p>
    <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
        <x-button :href="url()->previous() ?: route('home')">Go back and try again</x-button>
        <x-button :href="route('home')" variant="secondary">Home</x-button>
    </div>
</section>
@endsection
