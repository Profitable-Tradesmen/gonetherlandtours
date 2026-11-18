@if (count($d['items']))
<section class="bg-sand py-16 sm:py-24" aria-labelledby="is-{{ $sid }}">
    <div class="container-site">
        <x-section-heading data-reveal="up" :eyebrow="$d['eyebrow'] ?: null" :title="$d['heading']" :id="'is-'.$sid" />
        {{-- Phone/tablet: swipe cards. Desktop: a strip of tall photo panels; the hovered (or first) panel opens up. --}}
        <x-slider class="expand-strip grid gap-6 sm:grid-cols-2">
            @foreach ($d['items'] as $i => $p)
                <article class="strip-panel group relative isolate flex min-h-72 flex-col justify-end overflow-hidden rounded-2xl p-6 text-white shadow-card" data-reveal="up" style="--d:{{ ($i % 3) * 120 }}ms" tabindex="0">
                    @if ($p['image'])<x-picture :src="$p['image']" :alt="$p['image_alt']" :width="2000" :height="1116" class="absolute inset-0 -z-20 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" />@endif
                    <div class="absolute inset-0 -z-10 bg-gradient-to-t from-ink/85 via-ink/30 to-transparent"></div>
                    <p class="label-caps text-accent">{{ sprintf('%02d', $i + 1) }}</p>
                    <h3 class="strip-title mt-1 text-2xl font-medium">{{ $p['title'] }}</h3>
                    <div class="strip-more">
                        <p class="mt-1 text-sm text-white/85">{{ $p['text'] }}</p>
                        @if ($d['button_label'])<button type="button" class="inline-flex min-h-[2.75rem] items-center mt-1 self-start text-sm font-bold text-accent underline underline-offset-4" @click="$store.modal.show()">{{ $d['button_label'] }}</button>@endif
                    </div>
                </article>
            @endforeach
        </x-slider>
    </div>
</section>
@endif
