@php $types = \App\Support\SiteData::tourTypes(); @endphp
@if ($types->isNotEmpty())
<section class="py-16 sm:py-24" aria-labelledby="tt-{{ $sid }}">
    <div class="container-site">
        <x-section-heading data-reveal="up" :eyebrow="$d['eyebrow'] ?: null" :title="$d['heading']" :subtitle="$d['intro'] ?: null" :id="'tt-'.$sid" />
        {{-- One navy card per tour type: photo, title, small tag, summary, three bullets and three actions. --}}
        <x-slider class="grid gap-6 md:grid-cols-3">
            @foreach ($types as $i => $t)
                @php $points = array_slice($t->cardPointList(), 0, 3); @endphp
                <article class="group flex flex-col overflow-hidden rounded-xl bg-brand-900 text-white shadow-card" data-reveal="up" style="--d:{{ $i * 120 }}ms">
                    <a href="{{ route('tour-types.show', $t) }}" class="relative block overflow-hidden" tabindex="-1" aria-hidden="true">
                        <x-picture :src="$t->image" :alt="$t->image_alt" :width="960" :height="640" class="aspect-[3/2] w-full object-cover transition-transform duration-500 group-hover:scale-105" sizes="(min-width:768px) 33vw, 100vw" />
                        <span class="absolute inset-0 bg-gradient-to-t from-brand-900/80 to-transparent"></span>
                    </a>
                    <div class="flex flex-1 flex-col p-6">
                        <h3 class="text-2xl font-medium"><a href="{{ route('tour-types.show', $t) }}" class="hover:text-accent">{{ $t->name }}</a></h3>
                        @if ($t->card_tag)<p class="label-caps mt-2 text-[0.68rem] text-accent">{{ $t->card_tag }}</p>@endif
                        <p class="mt-3 text-sm leading-relaxed text-white/85">{{ $t->summary }}</p>
                        @if ($points)
                            <ul class="mt-4 space-y-2 text-sm font-semibold">
                                @foreach ($points as $pt)<li class="flex items-start gap-2"><x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0 text-accent" />{{ $pt }}</li>@endforeach
                            </ul>
                        @endif
                        <a href="{{ route('tour-types.show', $t) }}" class="mt-5 inline-flex min-h-[2.75rem] items-center gap-2 text-sm font-bold hover:text-accent">{{ $d['more_label'] }} <x-icon name="arrow" class="h-4 w-4" /></a>
                        <div class="mt-auto grid grid-cols-2 gap-3 pt-3">
                            @if ($d['quote_label'])<button type="button" class="btn btn-primary w-full !px-3" @click="$store.modal.show()">{{ $d['quote_label'] }}</button>@endif
                            @if ($d['ask_label'])<a href="{{ route('contact', ['topic' => $t->slug]) }}" class="btn btn-outline w-full !px-3 text-white">{{ $d['ask_label'] }}</a>@endif
                        </div>
                    </div>
                </article>
            @endforeach
        </x-slider>
    </div>
</section>
@endif
