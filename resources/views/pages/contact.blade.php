@extends('layouts.app')

@section('content')
<section class="bg-brown py-12 text-white sm:py-16"><div class="container-site">
    <nav aria-label="Breadcrumb" class="mb-4 text-xs text-white/70"><a href="{{ route('home') }}" class="hover:text-accent">Home</a> / <span>Contact</span></nav>
    <h1 class="text-4xl font-medium sm:text-5xl">{{ $page->h1 }}</h1>
</div></section>
<section class="container-site grid gap-10 py-12 lg:grid-cols-[1fr_1.2fr]">
    <div>
        <div class="prose-site">{!! clean_html($page->body) !!}</div>
        <ul class="mt-6 space-y-3 text-sm">
            @if (setting('phone'))<li class="flex items-center gap-2"><x-icon name="phone" class="h-5 w-5 text-rust" /><a href="tel:{{ preg_replace('/[^+\d]/', '', setting('phone')) }}" class="font-semibold">{{ setting('phone') }}</a></li>@endif
            @if (setting('email'))<li class="flex items-center gap-2"><x-icon name="mail" class="h-5 w-5 text-rust" /><a href="mailto:{{ setting('email') }}" class="font-semibold">{{ setting('email') }}</a></li>@endif
            @if (setting('address'))<li class="flex items-center gap-2"><x-icon name="map" class="h-5 w-5 text-rust" />{{ setting('address') }}</li>@endif
        </ul>
    </div>
    <div class="rounded-2xl border-4 border-accent bg-white p-5 shadow-card sm:p-6">
        <x-enquiry-form form="contact" idp="contact" extra submit="Send Message" />
    </div>
</section>
@endsection
