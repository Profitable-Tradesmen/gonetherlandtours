@extends('layouts.app', ['seo' => \App\Support\Seo::make(['title' => 'Too many requests | '.setting('brand_name', 'GoNetherlandTours'), 'noindex' => true])])

@section('content')
<section class="container-site py-24 text-center">
    <p class="label-caps text-rust">Slow down</p>
    <h1 class="mt-3 text-4xl font-medium sm:text-5xl">Too many attempts</h1>
    <p class="mx-auto mt-4 max-w-md text-ink/75">Please wait a minute and try again. If you keep seeing this, contact us and we will help.</p>
    <div class="mt-8"><x-button :href="route('home')">Back to home</x-button></div>
</section>
@endsection
