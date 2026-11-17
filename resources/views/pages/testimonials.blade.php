@extends('layouts.app')

@section('content')
<section class="bg-brand-900 py-14 text-center text-white sm:py-20">
    <div class="container-site">
        <nav aria-label="Breadcrumb" class="mb-4 text-xs text-white/70"><a href="{{ route('home') }}" class="hover:text-accent">Home</a> / <span>Testimonials</span></nav>
        <h1 class="text-4xl font-medium sm:text-5xl">What our travellers say</h1>
        <p class="mx-auto mt-3 max-w-2xl text-white/90">Real words from people who have travelled the Netherlands with us.</p>
    </div>
</section>
<section class="bg-sand py-14 sm:py-20"><div class="container-site">
    @if ($testimonials->isNotEmpty())
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($testimonials as $t)
                <figure class="relative rounded-2xl bg-white p-6 pt-10 shadow-card">
                    <span class="absolute left-5 top-1 font-display text-6xl leading-none text-accent" aria-hidden="true">&ldquo;</span>
                    <blockquote class="text-base leading-relaxed">{{ $t->quote }}</blockquote>
                    <figcaption class="mt-4 text-sm font-semibold text-brand-700">@if ($t->is_placeholder)<span class="label-caps mb-2 block text-[0.65rem] text-ink/60">Sample review</span>@endif{{ $t->name }}@if ($t->location), <span class="font-normal text-ink/70">{{ $t->location }}</span>@endif</figcaption>
                </figure>
            @endforeach
        </div>
    @else
        <p class="text-center">Reviews are coming soon.</p>
    @endif
    <div class="mt-12 text-center"><x-button @click="$store.modal.show()">Plan my Netherlands trip</x-button></div>
</div></section>
@endsection
