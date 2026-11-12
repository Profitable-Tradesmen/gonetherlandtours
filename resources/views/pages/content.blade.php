@extends('layouts.app')
{{-- Generic rich-text page: About, Terms, Privacy. [Company Name] in legal copy is replaced at render time. --}}
@section('content')
<section class="bg-brown py-12 text-white sm:py-16"><div class="container-site">
    <nav aria-label="Breadcrumb" class="mb-4 text-xs text-white/70"><a href="{{ route('home') }}" class="hover:text-accent">Home</a> / <span>{{ $page->title }}</span></nav>
    <h1 class="text-4xl font-medium sm:text-5xl">{{ $page->h1 ?: $page->title }}</h1>
</div></section>
<section class="container-site py-12">
    <div class="prose-site max-w-3xl">{!! str_replace('[Company Name]', e(company_name()), clean_html($page->body)) !!}</div>
    @if (in_array($page->slug, ['terms', 'privacy']))<p class="mt-8 text-xs text-ink/60">Version {{ $page->version }}</p>@endif
    @if ($page->slug === 'about')<p class="mt-8"><x-button @click="$store.modal.show()">Start Your Adventure</x-button></p>@endif
</section>
@endsection
