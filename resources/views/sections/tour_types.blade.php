@php $types = \App\Support\SiteData::tourTypes(); @endphp
@if ($types->isNotEmpty())
<section class="py-16 sm:py-24" aria-labelledby="tt-{{ $sid }}">
    <div class="container-site">
        <x-section-heading data-reveal="up" :eyebrow="$d['eyebrow'] ?: null" :title="$d['heading']" :id="'tt-'.$sid" />
    </div>
    {{-- Edge-to-edge photo cards: the title, a short line and the quote button sit on the photo. --}}
    {{-- Phones: a swipeable row (next card peeking, dash indicators). From md: three columns. --}}
    <div x-data="carousel" role="region" aria-roledescription="carousel" aria-label="{{ $d['heading'] }}">
    <div x-ref="track" @scroll.passive="sync()" class="carousel-track flex snap-x snap-mandatory gap-3 overflow-x-auto px-3 pb-2 md:grid md:grid-cols-3 md:gap-5 md:overflow-visible md:px-5 md:pb-0">
        @foreach ($types as $i => $t)
            <article data-slide class="group relative isolate flex min-h-[26rem] w-[85%] shrink-0 snap-center md:w-auto flex-col justify-end overflow-hidden rounded-2xl text-white sm:min-h-[30rem] lg:min-h-[34rem]" data-reveal="up" style="--d:{{ $i * 120 }}ms">
                <x-picture :src="$t->image" :alt="$t->image_alt" :width="960" :height="640" class="absolute inset-0 -z-20 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" sizes="(min-width:768px) 33vw, 100vw" />
                <span class="absolute inset-0 -z-10 bg-gradient-to-t from-ink/90 via-ink/40 to-transparent"></span>
                <div class="p-6 sm:p-8">
                    <h3 class="text-3xl font-medium"><a href="{{ route('tour-types.show', $t) }}" class="after:absolute after:inset-0 hover:text-accent">{{ $t->name }}</a></h3>
                    <p class="mt-2 text-sm leading-relaxed text-white/90">{{ $t->summary }}</p>
                    <p class="relative z-10 mt-5 flex flex-wrap gap-3">
                        @if ($d['quote_label'])<button type="button" class="btn btn-primary !px-5 !py-2.5" @click="$store.modal.show()">{{ $d['quote_label'] }}</button>@endif
                        @if ($d['ask_label'])<a href="{{ route('contact', ['topic' => $t->slug]) }}" class="btn btn-outline !px-5 !py-2.5 text-white">{{ $d['ask_label'] }}</a>@endif
                    </p>
                </div>
            </article>
        @endforeach
    </div>
                <div class="mt-3 flex justify-center gap-2 md:hidden" x-show="count > 1" x-cloak>
                    <template x-for="n in count" :key="n">
                        <button type="button" class="h-1.5 w-10 rounded-sm transition-colors" :class="n - 1 === idx ? 'bg-brand-700' : 'bg-brand-700/25'" @click="go(n - 1)" :aria-label="'Show tour type ' + n" :aria-current="n - 1 === idx"></button>
                    </template>
                </div>
    </div>
</section>
@endif
