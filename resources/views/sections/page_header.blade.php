@php
    $center = $d['align'] === 'center';
    $heading = $d['heading'] ?: ($page->h1 ?: $page->title);
    $photo = $d['image'] ?? '';
@endphp
<section class="{{ $photo ? 'relative isolate overflow-hidden py-16 sm:py-24' : 'py-12 sm:py-16' }} {{ $d['tone'] === 'blue' ? 'bg-brand-900' : 'bg-brown' }} text-white {{ $center ? 'text-center' : '' }}">
    @if ($photo)
        <x-picture :src="$photo" alt="" :width="1600" :height="640" priority class="absolute inset-0 -z-20 h-full w-full object-cover" sizes="100vw" />
        <div class="absolute inset-0 -z-10 bg-gradient-to-r from-ink/75 via-ink/45 to-ink/20"></div>
    @endif
    <div class="container-site">
    <nav aria-label="Breadcrumb" class="mb-4 text-xs text-white/80"><a href="{{ route('home') }}" class="hover:text-accent">Home</a> / <span>{{ $page->title }}</span></nav>
    <h1 class="text-4xl font-medium sm:text-5xl {{ $photo ? '[text-shadow:0_1px_10px_rgb(12_13_17/0.5)]' : '' }}">{!! emphasize($heading, 'text-accent') !!}</h1>
    @if ($d['subtitle'])<p class="mt-3 max-w-2xl text-white/90 {{ $center ? 'mx-auto' : '' }}">{{ $d['subtitle'] }}</p>@endif
</div></section>
