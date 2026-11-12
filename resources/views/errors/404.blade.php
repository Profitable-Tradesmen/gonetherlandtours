@extends('layouts.app', ['seo' => \App\Support\Seo::make(['title' => 'Page not found | '.setting('brand_name', 'GoNetherlandTours'), 'noindex' => true])])

@section('content')
<section class="container-site py-24 text-center">
    <p class="label-caps text-rust">Error 404</p>
    <h1 class="mt-3 text-4xl font-medium sm:text-5xl">We could not find that page</h1>
    <p class="mx-auto mt-4 max-w-md text-ink/75">The page may have moved. Try our tour packages or get in touch and we will help.</p>
    <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
        <x-button :href="route('home')">Back to home</x-button>
        <x-button :href="route('packages.index')" variant="secondary">Tour packages</x-button>
    </div>
</section>
@endsection
