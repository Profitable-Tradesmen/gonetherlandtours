@php $types = \App\Support\SiteData::tourTypes(); @endphp
@if ($types->isNotEmpty())
{{-- Dark navy band so it clearly reads as a different section from the light ones around it. Square, edge-to-edge photo cards: the title,
     a short line, the "how you travel" note and one outlined quote button sit on the photo. --}}
<section class="bg-brand-900 pb-5 pt-16 sm:pt-20" aria-labelledby="tt-{{ $sid }}">
    <div class="container-site">
        <x-section-heading data-reveal="up" :light="true" :eyebrow="$d['eyebrow'] ?: null" :title="$d['heading']" :id="'tt-'.$sid" />
    </div>
    <div class="grid gap-5 md:grid-cols-3">
        @foreach ($types as $i => $t)
            <article class="group relative isolate flex min-h-[24rem] flex-col justify-end overflow-hidden text-white sm:min-h-[28rem] lg:min-h-[31rem]" data-reveal="up" style="--d:{{ $i * 120 }}ms">
                <x-picture :src="$t->image" :alt="$t->image_alt" :width="960" :height="640" class="absolute inset-0 -z-20 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" sizes="(min-width:768px) 33vw, 100vw" />
                <span class="absolute inset-0 -z-10 bg-gradient-to-t from-ink/90 via-ink/35 to-transparent"></span>
                <div class="p-6 sm:p-8">
                    <h3 class="text-3xl font-medium"><a href="{{ route('tour-types.show', $t) }}" class="after:absolute after:inset-0 hover:text-accent">{{ $t->name }}</a></h3>
                    <p class="mt-1 line-clamp-2 text-sm text-white">{{ $t->summary }}</p>
                    @if ($t->style_note ?? null)<p class="label-caps mt-4 text-[0.7rem] text-white/85">{{ $t->style_note }}</p>@endif
                    @if ($d['quote_label'])
                        <p class="relative z-10 mt-5"><button type="button" class="inline-flex min-h-[2.75rem] items-center border border-white px-8 py-2.5 text-sm font-semibold uppercase tracking-wide transition-colors hover:bg-white hover:text-ink" @click="$store.modal.show()">{{ $d['quote_label'] }}</button></p>
                    @endif
                </div>
            </article>
        @endforeach
    </div>
</section>
@endif
