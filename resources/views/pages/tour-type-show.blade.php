@extends('layouts.app')

@section('content')
<section class="relative isolate overflow-hidden bg-brown py-14 text-white sm:py-20">
    @if ($tourType->image)<x-picture :src="$tourType->image" :alt="$tourType->image_alt ?? ''" :width="960" :height="1430" priority class="absolute inset-0 -z-20 h-full w-full object-cover" />@endif
    <div class="absolute inset-0 -z-10 bg-ink/65"></div>
    <div class="container-site">
        <nav aria-label="Breadcrumb" class="mb-4 text-xs text-white/70"><a href="{{ route('home') }}" class="hover:text-accent">Home</a> / <a href="{{ route('packages.index') }}" class="hover:text-accent">Packages</a> / <span>{{ $tourType->name }}</span></nav>
        <h1 class="text-4xl font-medium sm:text-5xl">{{ $tourType->h1 ?: $tourType->name.' Netherlands Tours' }}</h1>
        <p class="mt-3 max-w-2xl text-white/90">{{ $tourType->summary }}</p>
        @if ($tourType->style_note)<p class="mt-3 flex min-h-[2.5rem] items-start gap-2 text-sm font-semibold text-white sm:min-h-0 sm:items-center"><x-icon name="guide" class="mt-0.5 h-5 w-5 shrink-0 text-accent sm:mt-0" />{{ $tourType->style_note }}</p>@endif
        <x-button class="mt-6" @click="$store.modal.show()">Get a Free Quote</x-button>
    </div>
</section>
@if (filled($tourType->body))
<section class="container-site py-12">
    <div class="prose-site max-w-3xl">{!! clean_html($tourType->body) !!}</div>
</section>
@endif
@if (! empty($sections))
    {{-- The rest of the page is built from sections (Admin > Pages > "{{ $tourType->name }} page"), the same blocks as the home page. --}}
    @foreach ($sections as $section)
        @include('sections.'.$section['type'], ['d' => $section['d'], 'sid' => $section['id'], 'tourType' => $tourType])
    @endforeach
@else
<section class="bg-sand py-14"><div class="container-site">
    <x-section-heading :title="$tourType->name.' packages'" />
    @if ($packages->isNotEmpty())
        <x-slider class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">@foreach ($packages as $p)@include('partials.package-card', ['package' => $p])@endforeach</x-slider>
    @else
        <p class="mt-6 text-center">No {{ strtolower($tourType->name) }} packages are published yet. <button type="button" class="font-bold text-brand-700 underline underline-offset-4" @click="$store.modal.show()">Ask us for a tailored quote</button>.</p>
    @endif
    <p class="mt-8 text-center"><a href="{{ route('packages.index') }}" class="font-bold text-brand-700 underline underline-offset-4 hover:text-rust">See all Netherlands packages</a></p>
</div></section>
@include('partials.why-us')
@endif
@if (empty($sections) && $faqs->isNotEmpty())
<section class="py-14"><div class="container-site max-w-3xl">
    <x-section-heading eyebrow="FAQ" title="Questions we are asked" />
    <div class="space-y-3">@foreach ($faqs as $f)<x-faq-item :question="$f->question" name="faq-tour-type">{{ $f->answer }}</x-faq-item>@endforeach</div>
    <p class="mt-6 text-center"><a href="{{ route('faq') }}" class="font-bold text-brand-700 underline underline-offset-4 hover:text-rust">Read all FAQs</a></p>
</div></section>
@endif
@endsection
