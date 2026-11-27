@extends('layouts.app')

@section('content')
<section class="bg-sand py-12 sm:py-16">
    <div class="container-site">
        <nav aria-label="Breadcrumb" class="mb-4 text-xs text-ink/70"><a href="{{ route('home') }}" class="underline hover:text-rust">Home</a> / <span>Photo credits</span></nav>
        <h1 class="text-4xl font-medium sm:text-5xl">Photo credits</h1>
        <p class="mt-4 max-w-2xl text-ink/80">With thanks to the photographers who share their work. These photos are used under the license named beside each one and have been resized, cropped and compressed for this website.</p>
    </div>
</section>
<section class="bg-white py-12 sm:py-16" aria-label="List of photo credits">
    <div class="container-site">
        <ul class="grid gap-5 md:grid-cols-2">
            @foreach ($credits as $c)
                <li class="flex min-w-0 items-start gap-4 rounded-2xl border border-ink/10 p-3">
                    @if ($c->file && is_file(public_path($c->file)))
                        <x-picture :src="$c->file" alt="" :width="1600" :height="1067" class="h-20 w-28 shrink-0 rounded-xl object-cover" sizes="112px" />
                    @endif
                    <div class="min-w-0 break-words text-sm">
                        <p class="font-bold text-brand-900">{{ $c->title }}</p>
                        <p class="mt-1">Photo: {{ $c->author }}</p>
                        <p class="mt-1 flex flex-wrap gap-x-4">
                            @if ($c->license_url)<a href="{{ $c->license_url }}" rel="noopener license" target="_blank" class="inline-block py-1 underline hover:text-rust">{{ $c->license }}</a>@else<span class="py-1">{{ $c->license }}</span>@endif
                            @if ($c->source_url)<a href="{{ $c->source_url }}" rel="noopener" target="_blank" class="inline-block py-1 underline hover:text-rust">Original photo<span class="sr-only">: {{ $c->title }}</span></a>@endif
                        </p>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</section>
@endsection
