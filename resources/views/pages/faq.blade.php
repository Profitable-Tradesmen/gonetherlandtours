@extends('layouts.app')

@section('content')
<section class="bg-brand-900 py-14 text-center text-white sm:py-20">
    <div class="container-site">
        <nav aria-label="Breadcrumb" class="mb-4 text-xs text-white/70"><a href="{{ route('home') }}" class="hover:text-accent">Home</a> / <span>FAQ</span></nav>
        <h1 class="text-4xl font-medium sm:text-5xl">Frequently asked questions</h1>
        <p class="mx-auto mt-3 max-w-2xl text-white/90">Quick answers about planning a Netherlands trip with us. Cannot find yours? Ask and we will reply.</p>
    </div>
</section>
<section class="py-14 sm:py-20"><div class="container-site max-w-3xl">
    @if ($faqs->isNotEmpty())
        <div class="space-y-3">@foreach ($faqs as $f)<x-faq-item :question="$f->question">{{ $f->answer }}</x-faq-item>@endforeach</div>
    @else
        <p class="text-center">No questions have been published yet.</p>
    @endif
    <div class="mt-10 rounded-2xl bg-sand p-6 text-center">
        <p class="font-display text-2xl">Still have a question?</p>
        <p class="mt-1 text-sm text-ink/70">Tell us about your trip and a travel specialist will get back to you.</p>
        <div class="mt-4 flex flex-wrap justify-center gap-3"><x-button @click="$store.modal.show()">Get a Free Quote</x-button><x-button :href="route('contact')" variant="secondary">Contact us</x-button></div>
    </div>
</div></section>
@endsection
