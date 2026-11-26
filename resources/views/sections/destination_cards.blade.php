@php
    $dests = \App\Models\Destination::active()->orderBy('sort_order')->get();
    // one query for the number of tours per destination
    $stats = \App\Models\Package::published()->selectRaw('destination_id, count(*) as n, min(nights) as nmin, max(nights) as nmax')->groupBy('destination_id')->get()->keyBy('destination_id');
@endphp
@if ($dests->isNotEmpty())
<section class="{{ ($d['background'] ?? 'sand') === 'white' ? 'bg-white' : 'bg-sand' }} py-16 sm:py-24" aria-labelledby="dc-{{ $sid }}">
    <div class="container-site">
        <x-section-heading data-reveal="up" :eyebrow="$d['eyebrow'] ?: null" :title="$d['heading']" :subtitle="$d['text'] ?: null" :id="'dc-'.$sid" />
        {{-- Same card design as the package cards: photo with a navy tab and a count along the bottom, a pin label, title, two-line text, an Explore link,
             and a footer row with the Explore link and the quote button. Phones: swipe carousel with dots; from sm: a grid. --}}
        <x-slider class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($dests as $i => $dest)
                @php $st = $stats->get($dest->id); $url = route('destinations.show', $dest); @endphp
                <article class="group flex h-full flex-col overflow-hidden rounded-2xl border border-ink/10 bg-white text-ink shadow-card transition duration-300 hover:-translate-y-1 hover:shadow-[0_24px_44px_-18px_rgb(12_13_17/0.4)]" data-reveal="up" style="--d:{{ ($i % 3) * 120 }}ms">
                    <a href="{{ $url }}" class="relative block overflow-hidden" tabindex="-1" aria-hidden="true">
                        @if ($dest->image)<x-picture :src="$dest->image" :alt="$dest->image_alt" :width="2000" :height="1116" class="aspect-[3/2] w-full object-cover transition-transform duration-700 group-hover:scale-105" sizes="(min-width:1024px) 33vw, (min-width:640px) 50vw, 88vw" />@else<div class="aspect-[3/2] w-full bg-brand-900"></div>@endif
                        <span class="absolute left-0 top-4 bg-brand-900 px-3 py-1.5 text-[0.7rem] font-bold uppercase tracking-[0.14em] text-white shadow-sm">Destination</span>
                        @if ($st)<span class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-ink/70 to-transparent px-4 pb-3 pt-10 text-sm font-semibold text-white">{{ $st->n }} {{ \Illuminate\Support\Str::plural('tour', $st->n) }}</span>@endif
                    </a>
                    <div class="flex min-w-0 flex-1 flex-col p-5 sm:p-6">
                        <p class="label-caps flex items-center gap-1.5 text-xs font-bold text-rust"><x-icon name="map" class="h-4 w-4" />@if ($st && $st->nmin){{ $st->nmin == $st->nmax ? $st->nmin : $st->nmin.'–'.$st->nmax }} nights @else Trips across the country @endif</p>
                        <h3 class="mt-2.5 line-clamp-2 text-[1.45rem] font-medium leading-snug"><a href="{{ $url }}" class="hover:text-rust">{{ $dest->name }}</a></h3>
                        <p class="mt-2 line-clamp-2 min-h-[3rem] flex-1 text-[0.95rem] leading-relaxed text-ink/80">{{ $dest->summary }}</p>
                        @if ($places = array_filter(array_map('trim', explode(',', (string) $dest->places))))
                            <ul class="mt-4 flex flex-wrap gap-x-4 gap-y-2" aria-label="Places in {{ $dest->name }}">
                                @foreach ($places as $place)<li class="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-900"><i class="h-1.5 w-1.5 rotate-45 bg-accent" aria-hidden="true"></i>{{ $place }}</li>@endforeach
                            </ul>
                        @endif
                        <div class="mt-5 flex items-center justify-between gap-3 border-t border-sand-200 pt-3">
                            <a href="{{ $url }}" class="group/link inline-flex min-h-[2.75rem] items-center gap-1.5 text-sm font-bold text-brand-700 hover:text-rust">
                                <span class="border-b border-current pb-0.5">{{ $d['explore_label'] ?: 'Explore' }}<span class="sr-only"> {{ $dest->name }}</span></span>
                                <x-icon name="arrow" class="h-4 w-4 transition-transform group-hover/link:translate-x-1" />
                            </a>
                            @if ($d['quote_label'])<button type="button" class="btn btn-primary shrink-0 !px-5" @click="$store.modal.show()">{{ $d['quote_label'] }}</button>@endif
                        </div>
                    </div>
                </article>
            @endforeach
        </x-slider>
        @if (! empty($d['own_heading']))
            <div class="mt-6 flex flex-col items-start gap-5 rounded-2xl bg-brand-900 p-6 text-white shadow-card sm:p-8 md:flex-row md:items-center md:justify-between" data-reveal="up">
                <div class="flex min-w-0 items-start gap-4">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-accent text-ink"><x-icon name="map" class="h-6 w-6" /></span>
                    <div class="min-w-0">
                        <h3 class="text-2xl font-medium">{{ $d['own_heading'] }}</h3>
                        @if (! empty($d['own_text']))<p class="mt-1 max-w-2xl leading-relaxed text-white/85">{{ $d['own_text'] }}</p>@endif
                    </div>
                </div>
                @if (! empty($d['own_button']))<x-button class="shrink-0" @click="$store.modal.show()">{{ $d['own_button'] }} <x-icon name="arrow" class="h-4 w-4" /></x-button>@endif
            </div>
        @endif
        @if ($d['all_label'])<p class="mt-10 text-center"><x-cta-button variant="secondary" :label="$d['all_label']" :url="$d['all_url']" /></p>@endif
    </div>
</section>
@endif
