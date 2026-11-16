@extends('layouts.app')

@section('content')
<section class="bg-brown py-12 text-white sm:py-16">
    <div class="container-site">
        <nav aria-label="Breadcrumb" class="mb-4 text-xs text-white/70"><a href="{{ route('home') }}" class="hover:text-accent">Home</a> / <span>Blog</span></nav>
        <h1 class="text-4xl font-medium sm:text-5xl">Travel journal</h1>
        <p class="mt-3 max-w-2xl text-white/85">Guides, hidden gems and inspiration for your Netherlands trip.</p>
    </div>
</section>

<section class="py-10 sm:py-14">
    <div class="container-site">
        @if ($posts->isEmpty())
            <p class="rounded-xl bg-sand p-6 text-center">No articles yet. Check back soon, or <a href="{{ route('contact') }}" class="font-semibold text-rust underline">ask us anything about your trip</a>.</p>
        @else
            <x-slider class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)@include('partials.blog-card', ['post' => $post])@endforeach
            </x-slider>
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
@endsection
