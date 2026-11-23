@extends('layouts.app')

@section('content')
<x-page-banner :image="$post->image" :alt="$post->image_alt ?? ''" :heading="e($post->title)" :eyebrow="$post->label">
    <x-slot:crumbs><nav aria-label="Breadcrumb" class="mb-4 text-xs text-white/80"><a href="{{ route('home') }}" class="hover:text-accent">Home</a> / <a href="{{ route('blog.index') }}" class="hover:text-accent">Blog</a> / <span>{{ $post->title }}</span></nav></x-slot:crumbs>
    <p class="mt-3 text-sm text-white/85">@if ($post->published_at)<time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('F j, Y') }}</time> &middot; @endif{{ $post->reading_minutes }} min read</p>
</x-page-banner>

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
