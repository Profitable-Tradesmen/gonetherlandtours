@extends('layouts.app')

@section('content')
<x-page-banner image="images/dest-amsterdam.webp" heading="Travel journal" text="Guides, hidden gems and inspiration for your Netherlands trip.">
    <x-slot:crumbs><nav aria-label="Breadcrumb" class="mb-4 text-xs text-white/80"><a href="{{ route('home') }}" class="hover:text-accent">Home</a> / <span>Blog</span></nav></x-slot:crumbs>
</x-page-banner>

<section class="py-10 sm:py-14">
    <div class="container-site">
        @if ($posts->isEmpty())
            <p class="rounded-xl bg-sand p-6 text-center">No articles yet. Check back soon, or <a href="{{ route('contact') }}" class="font-semibold text-rust underline">ask us anything about your trip</a>.</p>
        @else
            @php
                // the newest article is shown large at the top of the first page
                $featured = $posts->onFirstPage() && $posts->count() > 1 ? $posts->first() : null;
                $rest = $featured ? $posts->slice(1) : $posts;
            @endphp
            @if ($featured)
                <article class="group mb-10 grid overflow-hidden rounded-2xl bg-white shadow-card lg:grid-cols-2" data-reveal="up">
                    <a href="{{ route('blog.show', $featured) }}" class="block overflow-hidden" tabindex="-1" aria-hidden="true">
                        <x-picture :src="$featured->image ?: 'images/feature_band.webp'" :alt="$featured->image_alt ?? ''" :width="1600" :height="893" priority class="aspect-[3/2] h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" sizes="(min-width:1024px) 50vw, 100vw" />
                    </a>
                    <div class="flex flex-col justify-center p-6 sm:p-10">
                        <p class="label-caps text-rust">Latest{{ $featured->label ? ' · '.$featured->label : '' }}</p>
                        <p class="mt-1 text-xs text-ink/60">@if ($featured->published_at)<time datetime="{{ $featured->published_at->toDateString() }}">{{ $featured->published_at->format('M j, Y') }}</time> &middot; @endif{{ $featured->reading_minutes }} min read</p>
                        <h2 class="mt-3 text-3xl font-medium sm:text-4xl"><a href="{{ route('blog.show', $featured) }}" class="hover:text-rust">{{ $featured->title }}</a></h2>
                        @if ($featured->excerpt)<p class="mt-3 text-ink/75">{{ $featured->excerpt }}</p>@endif
                        <p class="mt-5"><a href="{{ route('blog.show', $featured) }}" class="btn btn-primary">Read the article<span class="sr-only"> about {{ $featured->title }}</span></a></p>
                    </div>
                </article>
            @endif
            @if ($rest->isNotEmpty())
                <x-slider class="grid gap-6 sm:grid-cols-2 {{ $rest->count() === 2 ? 'lg:grid-cols-2' : 'lg:grid-cols-3' }}">
                    @foreach ($rest as $post)@include('partials.blog-card', ['post' => $post])@endforeach
                </x-slider>
            @endif
            @if ($posts->hasPages())
                <nav class="mt-10 flex items-center justify-center gap-6 text-sm font-bold" aria-label="Pagination">
                    @if ($posts->previousPageUrl())<a href="{{ $posts->previousPageUrl() }}" rel="prev" class="text-brand-700 underline underline-offset-4 hover:text-rust">&larr; Newer</a>@endif
                    <span class="text-ink/60">Page {{ $posts->currentPage() }} of {{ $posts->lastPage() }}</span>
                    @if ($posts->nextPageUrl())<a href="{{ $posts->nextPageUrl() }}" rel="next" class="text-brand-700 underline underline-offset-4 hover:text-rust">Older &rarr;</a>@endif
                </nav>
            @endif
        @endif
    </div>
</section>

<section class="bg-sand py-14 sm:py-20" aria-labelledby="blog-cta">
    <div class="container-site text-center" data-reveal="up">
        <h2 id="blog-cta" class="text-3xl font-medium sm:text-4xl">Ready to plan your <em class="italic text-rust">Netherlands trip?</em></h2>
        <p class="mx-auto mt-3 max-w-xl text-ink/75">Tell us what caught your eye and a travel specialist will build a trip around it, with a clear, no-obligation quote.</p>
        <p class="mt-6 flex flex-wrap items-center justify-center gap-3"><x-button @click="$store.modal.show()">Get a free quote</x-button><a href="{{ route('packages.index') }}" class="btn btn-outline">Browse our packages</a></p>
    </div>
</section>
@endsection
