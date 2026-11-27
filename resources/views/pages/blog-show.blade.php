@extends('layouts.app')

@section('content')
<x-page-banner :image="$post->image" :alt="$post->image_alt ?? ''" :heading="e($post->title)" :eyebrow="$post->label">
    <x-slot:crumbs><nav aria-label="Breadcrumb" class="mb-4 text-xs text-white/80"><a href="{{ route('home') }}" class="hover:text-accent">Home</a> / <a href="{{ route('blog.index') }}" class="hover:text-accent">Blog</a> / <span>{{ $post->title }}</span></nav></x-slot:crumbs>
    <p class="mt-3 text-sm text-white/85">@if ($post->published_at)<time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('F j, Y') }}</time> &middot; @endif{{ $post->reading_minutes }} min read</p>
</x-page-banner>

<div class="container-site grid items-start gap-10 py-12 lg:grid-cols-[minmax(0,1.75fr)_minmax(0,1fr)] lg:gap-16 lg:py-16">
    <article class="min-w-0">
        @if ($post->excerpt)<p class="border-l-[3px] border-accent pl-5 font-display text-xl leading-relaxed text-brand-900 sm:text-2xl">{{ $post->excerpt }}</p>@endif
        <div class="prose-site mt-6">{!! clean_html($post->body) !!}</div>
        <p class="mt-10 flex flex-wrap items-center gap-3 border-t border-sand-200 pt-6"><x-button @click="$store.modal.show()">Get a free quote</x-button><a href="{{ route('blog.index') }}" class="btn btn-outline text-brand-900">Back to the journal</a></p>
    </article>
    <aside class="space-y-6 lg:sticky lg:top-28" aria-label="Plan your trip">
        <div class="rounded-2xl border-t-[3px] border-accent bg-brand-900 p-6 text-white shadow-card">
            <p class="font-display text-2xl font-medium">Want help planning this trip?</p>
            <p class="mt-3 text-sm leading-relaxed text-white/85">Tell us your dates and what you enjoy, and a travel specialist will send a clear, no-obligation quote.</p>
            <x-button class="mt-5 w-full" @click="$store.modal.show()">Plan my trip</x-button>
        </div>
        @php $tours = \App\Models\Package::published()->where('is_featured', true)->with('images')->orderBy('sort_order')->take(3)->get(); @endphp
        @if ($tours->isNotEmpty())
            <div class="rounded-2xl bg-sand p-5">
                <p class="label-caps text-xs font-bold text-rust">Tours you may like</p>
                <ul class="mt-3 divide-y divide-sand-200">
                    @foreach ($tours as $p)
                        <li><a href="{{ route('packages.show', $p) }}" class="group flex items-center gap-3 py-3">
                            @if ($p->images->first())<x-picture :media="$p->images->first()" alt="" sizes="80px" class="h-16 w-20 shrink-0 rounded-lg object-cover" />@endif
                            <span class="min-w-0"><span class="block font-display leading-snug text-brand-900 group-hover:text-rust">{{ $p->title }}</span><span class="mt-0.5 block text-xs text-ink/70">{{ $p->nights }} nights{{ $p->price_from ? ' · From '.$p->priceLabel() : '' }}</span></span>
                        </a></li>
                    @endforeach
                </ul>
            </div>
        @endif
    </aside>
</div>

@if ($related->isNotEmpty())
<section class="bg-sand py-14"><div class="container-site">
    <x-section-heading title="More from the journal" />
    <x-slider class="grid gap-6 sm:grid-cols-2 {{ $related->count() === 2 ? 'lg:grid-cols-2' : 'lg:grid-cols-3' }}">@foreach ($related as $post)@include('partials.blog-card', ['post' => $post])@endforeach</x-slider>
</div></section>
@endif
@endsection
