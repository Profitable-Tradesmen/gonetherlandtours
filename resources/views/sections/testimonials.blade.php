@php
    $dark = $d['variant'] === 'dark';
    $items = \App\Models\Testimonial::active()->when((int) $d['limit'] > 0, fn ($q) => $q->take((int) $d['limit']))->get();
@endphp
@if ($items->isNotEmpty())
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
@elseif (! $dark && $d['heading'] === '')
    <section class="container-site py-16 text-center"><p>Reviews are coming soon.</p></section>
@endif
