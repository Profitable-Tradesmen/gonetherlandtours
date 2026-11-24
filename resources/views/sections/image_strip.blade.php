@if (count($d['items']))
<section class="bg-sand py-16 sm:py-24" aria-labelledby="is-{{ $sid }}">
    <div class="container-site">
        <x-section-heading data-reveal="up" :eyebrow="$d['eyebrow'] ?: null" :title="$d['heading']" :id="'is-'.$sid" />
        {{-- Traveler-type cards, kept simple: photo, title, one short line and a plain link. Phones: swipe row. From sm: a grid (two columns, three from lg),
             every card the same height, text always visible (no hover needed). --}}
        <x-slider class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($d['items'] as $i => $p)
                <article class="group flex flex-col overflow-hidden rounded-xl bg-white shadow-card" data-reveal="up" style="--d:{{ ($i % 3) * 120 }}ms">
                    <div class="overflow-hidden">
                        @if ($p['image'])<x-picture :src="$p['image']" :alt="$p['image_alt']" :width="2000" :height="1116" class="aspect-[3/2] w-full object-cover transition-transform duration-700 group-hover:scale-105" sizes="(min-width:1024px) 33vw, (min-width:640px) 50vw, 84vw" />@else<div class="aspect-[3/2] w-full bg-brand-900"></div>@endif
                    </div>
                    <div class="flex flex-1 flex-col p-5">
                        <h3 class="text-xl font-medium">{{ $p['title'] }}</h3>
                        <p class="mt-1.5 flex-1 text-sm leading-relaxed text-ink/70">{{ $p['text'] }}</p>
                        @if ($d['button_label'])<button type="button" class="mt-3 inline-flex min-h-[2.75rem] items-center gap-1.5 self-start text-sm font-bold text-brand-700 hover:text-rust" @click="$store.modal.show()">{{ $d['button_label'] }} <x-icon name="arrow" class="h-4 w-4" /></button>@endif
                    </div>
                </article>
            @endforeach
        </x-slider>
    </div>
</section>
@endif
