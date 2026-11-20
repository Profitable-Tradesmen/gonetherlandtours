@extends('layouts.app')

@section('content')
<section class="relative isolate overflow-hidden bg-brown py-14 text-white sm:py-20">
    @if ($post->image)<x-picture :src="$post->image" :alt="$post->image_alt ?? ''" :width="960" :height="640" priority class="absolute inset-0 -z-20 h-full w-full object-cover" />@endif
    <div class="absolute inset-0 -z-10 bg-ink/65"></div>
    <div class="container-site">
        <nav aria-label="Breadcrumb" class="mb-4 text-xs text-white/70"><a href="{{ route('home') }}" class="hover:text-accent">Home</a> / <a href="{{ route('blog.index') }}" class="hover:text-accent">Blog</a> / <span>{{ $post->title }}</span></nav>
        @if ($post->label)<p class="label-caps text-accent">{{ $post->label }}</p>@endif
        <h1 class="mt-2 max-w-3xl text-4xl font-medium sm:text-5xl">{{ $post->title }}</h1>
        <p class="mt-3 text-sm text-white/80">@if ($post->published_at)<time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('F j, Y') }}</time> &middot; @endif{{ $post->reading_minutes }} min read</p>
    </div>
</section>

<article class="container-site py-12">
    @if ($post->excerpt)<p class="max-w-3xl text-lg text-ink/80">{{ $post->excerpt }}</p>@endif
    <div class="prose-site mt-6 max-w-3xl">{!! clean_html($post->body) !!}</div>
    <aside class="mt-10 max-w-3xl rounded-2xl bg-sand p-6 sm:p-8" aria-label="Plan your trip">
        <p class="font-display text-2xl font-medium">Want help planning this trip?</p>
        <p class="mt-2 text-ink/75">Tell us your dates and what you enjoy, and a travel specialist will send a clear, no-obligation quote.</p>
        <p class="mt-4 flex flex-wrap items-center gap-3"><x-button @click="$store.modal.show()">Get a free quote</x-button><a href="{{ route('packages.index') }}" class="btn btn-outline">Browse our packages</a></p>
    </aside>
</article>

@if ($related->isNotEmpty())
<section class="bg-sand py-14"><div class="container-site">
    <x-section-heading title="More from the journal" />
    <x-slider class="grid gap-6 sm:grid-cols-2 {{ $related->count() === 2 ? 'lg:grid-cols-2' : 'lg:grid-cols-3' }}">@foreach ($related as $post)@include('partials.blog-card', ['post' => $post])@endforeach</x-slider>
</div></section>
@endif
@endsection
