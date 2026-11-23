@if (count($d['items']))
<section class="bg-sand py-16 sm:py-24" aria-labelledby="is-{{ $sid }}">
    <div class="container-site">
        <x-section-heading data-reveal="up" :eyebrow="$d['eyebrow'] ?: null" :title="$d['heading']" :id="'is-'.$sid" />
        {{-- Traveler-type cards: a photo with a number badge, then the title, a short text and a "plan this trip" link. Phones: swipe row. From sm: a grid
             (two columns, three from lg) in which every card is the same height and the text is always visible (no hover needed). --}}
        <x-slider class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($d['items'] as $i => $p)
                <article class="group flex flex-col overflow-hidden rounded-2xl bg-white shadow-card ring-1 ring-sand-200 transition duration-300 hover:-translate-y-1 hover:shadow-xl" data-reveal="up" style="--d:{{ ($i % 3) * 120 }}ms">
                    <div class="relative overflow-hidden">
                        @if ($p['image'])<x-picture :src="$p['image']" :alt="$p['image_alt']" :width="2000" :height="1116" class="aspect-[4/3] w-full object-cover transition-transform duration-700 group-hover:scale-105" sizes="(min-width:1024px) 33vw, (min-width:640px) 50vw, 84vw" />@else<div class="aspect-[4/3] w-full bg-brand-900"></div>@endif
                        <span class="absolute left-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-accent font-display text-lg text-ink shadow-card" aria-hidden="true">{{ sprintf('%02d', $i + 1) }}</span>
                    </div>
                    <div class="flex flex-1 flex-col p-6">
                        <h3 class="text-2xl font-medium">{{ $p['title'] }}</h3>
                        <p class="mt-2 flex-1 text-sm leading-relaxed text-ink/75">{{ $p['text'] }}</p>
                        @if ($d['button_label'])<button type="button" class="mt-4 inline-flex min-h-[2.75rem] items-center gap-2 self-start text-sm font-bold text-brand-700 underline underline-offset-4 hover:text-rust" @click="$store.modal.show()">{{ $d['button_label'] }} <x-icon name="arrow" class="h-4 w-4 transition-transform group-hover:translate-x-1" /></button>@endif
                    </div>
                </article>
            @endforeach
        </x-slider>
    </div>
</section>
@endif
