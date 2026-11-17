@php
    $center = $d['align'] === 'center';
    $heading = $d['heading'] ?: ($page->h1 ?: $page->title);
@endphp
<section class="{{ $d['tone'] === 'blue' ? 'bg-brand-900' : 'bg-brown' }} py-12 text-white sm:py-16 {{ $center ? 'text-center' : '' }}"><div class="container-site">
    <nav aria-label="Breadcrumb" class="mb-4 text-xs text-white/70"><a href="{{ route('home') }}" class="hover:text-accent">Home</a> / <span>{{ $page->title }}</span></nav>
    <h1 class="text-4xl font-medium sm:text-5xl">{!! emphasize($heading, 'text-accent') !!}</h1>
    @if ($d['subtitle'])<p class="mt-3 max-w-2xl text-white/90 {{ $center ? 'mx-auto' : '' }}">{{ $d['subtitle'] }}</p>@endif
</div></section>
