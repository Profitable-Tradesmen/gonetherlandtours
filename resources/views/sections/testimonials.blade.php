@php
    $variant = $d['variant'];
    $dark = $variant === 'dark';
    $spot = $variant === 'spotlight';
    $stars = (bool) ($d['show_stars'] ?? true);
    $bg = (string) ($d['bg'] ?? '');
    $items = \App\Models\Testimonial::active()->when((int) $d['limit'] > 0, fn ($q) => $q->take((int) $d['limit']))->get();
@endphp
@if ($items->isNotEmpty() && $spot)
{{-- Spotlight: one review at a time in a large card (round photo or initial, stars, big quote, name, place and trip), a picker made of the
     travelers' photos, arrows and keyboard use. The background is a photo, GIF or video chosen in the admin. All reviews are in the HTML. --}}
<section class="relative isolate overflow-hidden bg-brand-900 py-16 text-white sm:py-24" aria-labelledby="te-{{ $sid }}">
    @include('partials.section-bg', ['bg' => $bg, 'poster' => $d['poster'] ?? '', 'posterAlt' => $d['poster_alt'] ?? '', 'tint' => $d['overlay'] ?? 'medium'])
    <div class="container-site" x-data="{ i: 0, n: {{ $items->count() }}, go(k) { this.i = (k + this.n) % this.n } }" role="region" aria-roledescription="carousel" aria-label="{{ $d['heading'] ?: 'Reviews' }}">
        @if ($d['heading'])<x-section-heading data-reveal="up" :eyebrow="$d['eyebrow'] ?: null" :title="$d['heading']" light :id="'te-'.$sid" />@endif
        <div class="mx-auto max-w-4xl">
            <div class="relative overflow-hidden rounded-3xl p-6 shadow-card ring-1 ring-white/25 sm:p-10 {{ $bg !== '' ? 'bg-brand-900/80 backdrop-blur-sm' : 'bg-white/10' }}" data-reveal="up">
                <span class="pointer-events-none absolute -top-4 right-6 select-none font-display text-[9rem] leading-none text-accent/25" aria-hidden="true">&rdquo;</span>
                {{-- All reviews sit in the same grid cell, so the block never changes height. Switching is a gentle sequence: the old review
                     fades out (0.3s), then the new one fades in with a small upward drift (0.5s). Without JavaScript the first review shows. --}}
                <div class="grid">
                    @foreach ($items as $k => $t)
                        <figure class="review-fig col-start-1 row-start-1 {{ $k === 0 ? 'is-active' : '' }}" :class="{ 'is-active': i === {{ $k }} }" :aria-hidden="i !== {{ $k }}" :inert="i !== {{ $k }}">
                            <div class="grid items-center gap-6 md:grid-cols-[auto_1fr] md:gap-10">
                                <div class="flex justify-center">@include('partials.review-avatar', ['t' => $t, 'size' => 'h-24 w-24 text-4xl md:h-36 md:w-36 md:text-6xl', 'ring' => 'ring-4 ring-accent/70 ring-offset-4 ring-offset-brand-900'])</div>
                                <div class="text-center md:text-left">
                                    @if ($stars)<p>@include('partials.review-stars', ['rating' => $t->rating ?? 5, 'class' => 'h-5 w-5'])</p>@endif
                                    <blockquote class="mt-3 font-display text-xl leading-snug sm:text-2xl lg:text-[1.75rem]">{{ $t->quote }}</blockquote>
                                    <figcaption class="mt-5">
                                        @if ($t->is_placeholder)<span class="label-caps mb-1 block text-[0.65rem] text-white/60">Sample review</span>@endif
                                        <span class="block text-lg font-bold text-accent">{{ $t->name }}</span>
                                        <span class="mt-2 flex flex-wrap justify-center gap-2 text-xs font-semibold md:justify-start">
                                            @if ($t->location)<span class="rounded-full bg-white/15 px-3 py-1 text-white">{{ $t->location }}</span>@endif
                                            @if ($t->trip ?? null)<span class="rounded-full border border-white/40 px-3 py-1 text-white/90">{{ $t->trip }}</span>@endif
                                        </span>
                                    </figcaption>
                                </div>
                            </div>
                        </figure>
                    @endforeach
                </div>
            </div>
            @if ($items->count() > 1)
                <div class="mt-8 flex items-center justify-center gap-2 sm:gap-3">
                    <button type="button" class="flex h-11 w-11 items-center justify-center rounded-full border border-white/60 bg-brand-900/60 hover:bg-white/15" @click="go(i - 1)" aria-label="Previous review"><x-icon name="arrow" class="h-5 w-5 rotate-180" /></button>
                    @foreach ($items as $k => $t)
                        <button type="button" class="rounded-full border-2 p-0.5 transition" :class="i === {{ $k }} ? 'border-accent scale-110' : 'border-white/40 opacity-80 hover:opacity-100'" @click="i = {{ $k }}" :aria-current="i === {{ $k }}" aria-label="Show review {{ $k + 1 }} from {{ $t->name }}">@include('partials.review-avatar', ['t' => $t, 'size' => 'h-11 w-11 text-lg sm:h-12 sm:w-12'])</button>
                    @endforeach
                    <button type="button" class="flex h-11 w-11 items-center justify-center rounded-full border border-white/60 bg-brand-900/60 hover:bg-white/15" @click="go(i + 1)" aria-label="Next review"><x-icon name="arrow" class="h-5 w-5" /></button>
                </div>
            @endif
            @if ($d['button_label'])<div class="mt-10 text-center"><x-button @click="$store.modal.show()">{{ $d['button_label'] }}</x-button></div>@endif
        </div>
    </div>
</section>
@elseif ($items->isNotEmpty())
<section class="relative isolate overflow-hidden {{ $dark ? 'bg-brand-900 text-white' : 'bg-sand' }} py-16 sm:py-24" aria-labelledby="te-{{ $sid }}">
    @if ($dark)@include('partials.section-bg', ['bg' => $bg, 'poster' => $d['poster'] ?? '', 'posterAlt' => $d['poster_alt'] ?? '', 'tint' => $d['overlay'] ?? 'medium'])@endif
    <div class="container-site">
        @if ($d['heading'])<x-section-heading data-reveal="up" :eyebrow="$d['eyebrow'] ?: null" :title="$d['heading']" :light="$dark" :id="'te-'.$sid" />@endif
        <x-slider :light="$dark" class="grid gap-6 md:grid-cols-3">
            @foreach ($items as $t)
                <figure class="relative flex flex-col rounded-2xl {{ $dark ? ($bg !== '' ? 'bg-brand-900/80 backdrop-blur-sm' : 'bg-white/10').' ring-1 ring-white/20 transition-transform duration-300 hover:-translate-y-1' : 'bg-white shadow-card' }} p-6 pt-8" data-reveal="up" style="--d:{{ $loop->index * 120 }}ms">
                    <span class="absolute right-5 top-1 select-none font-display text-7xl leading-none text-accent/40" aria-hidden="true">&rdquo;</span>
                    @if ($stars)<p>@include('partials.review-stars', ['rating' => $t->rating ?? 5, 'class' => 'h-4 w-4'])</p>@endif
                    <blockquote class="mt-3 flex-1 text-base leading-relaxed">{{ $t->quote }}</blockquote>
                    <figcaption class="mt-5 flex items-center gap-3 border-t {{ $dark ? 'border-white/20' : 'border-sand-200' }} pt-4">
                        @include('partials.review-avatar', ['t' => $t, 'size' => 'h-12 w-12 text-lg'])
                        <span class="min-w-0 text-sm">
                            @if ($t->is_placeholder)<span class="label-caps mb-0.5 block text-[0.65rem] {{ $dark ? 'text-white/60' : 'text-ink/60' }}">Sample review</span>@endif
                            <span class="block font-bold {{ $dark ? 'text-accent' : 'text-brand-700' }}">{{ $t->name }}</span>
                            <span class="block {{ $dark ? 'text-white/75' : 'text-ink/70' }}">{{ collect([$t->location, $t->trip ?? null])->filter()->implode(' · ') }}</span>
                        </span>
                    </figcaption>
                </figure>
            @endforeach
        </x-slider>
        @if ($d['button_label'])<div class="mt-12 text-center"><x-button @click="$store.modal.show()">{{ $d['button_label'] }}</x-button></div>@endif
    </div>
</section>
@elseif (! $dark && ! $spot && $d['heading'] === '')
    <section class="container-site py-16 text-center"><p>Reviews are coming soon.</p></section>
@endif
