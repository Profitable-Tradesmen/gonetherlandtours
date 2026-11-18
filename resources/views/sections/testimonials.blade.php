@php
    $variant = $d['variant'];
    $dark = $variant === 'dark';
    $spot = $variant === 'spotlight';
    $items = \App\Models\Testimonial::active()->when((int) $d['limit'] > 0, fn ($q) => $q->take((int) $d['limit']))->get();
@endphp
@if ($items->isNotEmpty() && $spot)
{{-- Spotlight: one review at a time with a picker (initials), arrows and swipe-free keyboard use. All reviews are in the HTML. --}}
<section class="bg-brand-900 py-16 text-white sm:py-24" aria-labelledby="te-{{ $sid }}">
    <div class="container-site" x-data="{ i: 0, n: {{ $items->count() }}, go(k) { this.i = (k + this.n) % this.n } }" role="region" aria-roledescription="carousel" aria-label="{{ $d['heading'] ?: 'Reviews' }}">
        @if ($d['heading'])<x-section-heading data-reveal="up" :eyebrow="$d['eyebrow'] ?: null" :title="$d['heading']" light :id="'te-'.$sid" />@endif
        <div class="mx-auto max-w-3xl text-center">
            <span class="font-display text-7xl leading-none text-accent" aria-hidden="true">&ldquo;</span>
            {{-- All reviews sit in the same grid cell, so the block never changes height. Switching is a gentle sequence: the old review
                 fades out (0.3s), then the new one fades in with a small upward drift (0.5s). Without JavaScript the first review shows. --}}
            <div class="-mt-4 grid">
                @foreach ($items as $k => $t)
                    <figure class="review-fig col-start-1 row-start-1 {{ $k === 0 ? 'is-active' : '' }}" :class="{ 'is-active': i === {{ $k }} }" :aria-hidden="i !== {{ $k }}" :inert="i !== {{ $k }}">
                        <blockquote class="font-display text-2xl leading-snug sm:text-3xl">{{ $t->quote }}</blockquote>
                        <figcaption class="mt-6 text-sm font-semibold text-accent">
                            @if ($t->is_placeholder)<span class="label-caps mb-1 block text-[0.65rem] text-white/60">Sample review</span>@endif
                            {{ $t->name }}@if ($t->location), <span class="font-normal text-white/75">{{ $t->location }}</span>@endif
                        </figcaption>
                    </figure>
                @endforeach
            </div>
            @if ($items->count() > 1)
                <div class="mt-8 flex items-center justify-center gap-2 sm:gap-3">
                    <button type="button" class="flex h-11 w-11 items-center justify-center rounded-full border border-white/40 hover:bg-white/10" @click="go(i - 1)" aria-label="Previous review"><x-icon name="arrow" class="h-5 w-5 rotate-180" /></button>
                    @foreach ($items as $k => $t)
                        <button type="button" class="flex h-12 w-12 items-center justify-center rounded-full border-2 font-display text-lg transition" :class="i === {{ $k }} ? 'border-accent bg-accent text-ink' : 'border-white/30 text-white/80 hover:border-white'" @click="i = {{ $k }}" :aria-current="i === {{ $k }}" aria-label="Show review {{ $k + 1 }} from {{ $t->name }}">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($t->name, 0, 1)) }}</button>
                    @endforeach
                    <button type="button" class="flex h-11 w-11 items-center justify-center rounded-full border border-white/40 hover:bg-white/10" @click="go(i + 1)" aria-label="Next review"><x-icon name="arrow" class="h-5 w-5" /></button>
                </div>
            @endif
            @if ($d['button_label'])<div class="mt-10"><x-button @click="$store.modal.show()">{{ $d['button_label'] }}</x-button></div>@endif
        </div>
    </div>
</section>
@elseif ($items->isNotEmpty())
<section class="{{ $dark ? 'bg-brand-900 text-white' : 'bg-sand' }} py-16 sm:py-24" aria-labelledby="te-{{ $sid }}">
    <div class="container-site">
        @if ($d['heading'])<x-section-heading data-reveal="up" :eyebrow="$d['eyebrow'] ?: null" :title="$d['heading']" :light="$dark" :id="'te-'.$sid" />@endif
        <x-slider :light="$dark" class="grid gap-6 md:grid-cols-3">
            @foreach ($items as $t)
                <figure class="relative rounded-2xl {{ $dark ? 'bg-white/10 transition-transform duration-300 hover:-translate-y-1' : 'bg-white shadow-card' }} p-6 pt-10" data-reveal="up" style="--d:{{ $loop->index * 120 }}ms">
                    <span class="absolute left-5 top-1 font-display text-6xl leading-none text-accent" aria-hidden="true">&ldquo;</span>
                    <blockquote class="text-base leading-relaxed">{{ $t->quote }}</blockquote>
                    <figcaption class="mt-4 text-sm font-semibold {{ $dark ? 'text-accent' : 'text-brand-700' }}">@if ($t->is_placeholder)<span class="label-caps mb-2 block text-[0.65rem] {{ $dark ? 'text-white/60' : 'text-ink/60' }}">Sample review</span>@endif {{ $t->name }}@if ($t->location), <span class="font-normal {{ $dark ? 'text-white/75' : 'text-ink/70' }}">{{ $t->location }}</span>@endif</figcaption>
                </figure>
            @endforeach
        </x-slider>
        @if ($d['button_label'])<div class="mt-12 text-center"><x-button @click="$store.modal.show()">{{ $d['button_label'] }}</x-button></div>@endif
    </div>
</section>
@elseif (! $dark && ! $spot && $d['heading'] === '')
    <section class="container-site py-16 text-center"><p>Reviews are coming soon.</p></section>
@endif
