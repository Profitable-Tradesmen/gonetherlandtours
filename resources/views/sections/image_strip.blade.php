@if (count($d['items']))
<section class="bg-sand py-16 sm:py-24" aria-labelledby="is-{{ $sid }}">
    <div class="container-site">
        <x-section-heading data-reveal="up" :eyebrow="$d['eyebrow'] ?: null" :title="$d['heading']" :subtitle="$d['intro'] ?? null" :id="'is-'.$sid" />
        {{-- Traveler-type cards in the same design as the package cards: photo with a navy tab, a pin label, title, short text and a footer with a quote note and the button.
             Phones: swipe row. From sm: a grid (two columns, three from lg), every card the same height. --}}
        <x-slider class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($d['items'] as $i => $p)
                <article class="group flex h-full flex-col overflow-hidden rounded-2xl border border-ink/10 bg-white text-ink shadow-card transition duration-300 hover:-translate-y-1 hover:shadow-[0_24px_44px_-18px_rgb(12_13_17/0.4)]" data-reveal="up" style="--d:{{ ($i % 3) * 120 }}ms">
                    <div class="relative overflow-hidden">
                        @if ($p['image'])<x-picture :src="$p['image']" :alt="$p['image_alt']" :width="2000" :height="1116" class="aspect-[3/2] w-full object-cover transition-transform duration-700 group-hover:scale-105" sizes="(min-width:1024px) 33vw, (min-width:640px) 50vw, 88vw" />@else<div class="aspect-[3/2] w-full bg-brand-900"></div>@endif
                        <h3 class="absolute left-0 top-4 bg-brand-900 px-3 py-1.5 font-sans text-[0.7rem] font-bold uppercase tracking-[0.14em] text-white shadow-sm">{{ $p["title"] }}</h3>
                    </div>
                    <div class="flex min-w-0 flex-1 flex-col p-5 sm:p-6">
                        <p class="line-clamp-3 flex-1 text-[0.95rem] leading-relaxed text-ink/80">{{ $p['text'] }}</p>
                        @if ($d['button_label'])
                            <div class="mt-5 border-t border-sand-200 pt-3">
                                <button type="button" class="group/link inline-flex min-h-[2.75rem] items-center gap-1.5 text-sm font-bold text-brand-700 hover:text-rust" @click="$store.modal.show()"><span class="border-b border-current pb-0.5">{{ $d['button_label'] }}<span class="sr-only"> {{ $p['title'] }}</span></span><x-icon name="arrow" class="h-4 w-4 transition-transform group-hover/link:translate-x-1" /></button>
                            </div>
                        @endif
                    </div>
                </article>
            @endforeach
        </x-slider>
    </div>
</section>
@endif
