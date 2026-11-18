@php $types = \App\Support\SiteData::tourTypes(); @endphp
@if ($types->isNotEmpty())
<section class="py-16 sm:py-24" aria-labelledby="tt-{{ $sid }}">
    <div class="container-site">
        <x-section-heading data-reveal="up" :eyebrow="$d['eyebrow'] ?: null" :title="$d['heading']" :id="'tt-'.$sid" />
        <x-slider class="grid gap-6 md:grid-cols-3">
            @foreach ($types as $i => $t)
                <article class="group flex flex-col" data-reveal="up" style="--d:{{ $i * 120 }}ms">
                    <a href="{{ route('tour-types.show', $t) }}" class="block overflow-hidden rounded-2xl" tabindex="-1" aria-hidden="true">
                        <x-picture :src="$t->image" :alt="$t->image_alt" :width="960" :height="640" class="aspect-[3/2] w-full object-cover transition-transform duration-500 group-hover:scale-105" sizes="(min-width:768px) 33vw, 100vw" />
                    </a>
                    <p class="label-caps mt-5 text-rust">{{ sprintf('%02d', $i + 1) }}</p>
                    <h3 class="mt-1 text-2xl font-medium"><a href="{{ route('tour-types.show', $t) }}" class="hover:text-rust">{{ $t->name }}</a></h3>
                    <p class="mt-2 flex-1 text-sm leading-relaxed text-ink/75">{{ $t->summary }}</p>
                    <p class="mt-2 flex flex-wrap gap-x-5 text-sm font-bold">
                        @if ($d['quote_label'])<button type="button" class="inline-flex min-h-[2.75rem] items-center text-brand-700 underline underline-offset-4 hover:text-rust" @click="$store.modal.show()">{{ $d['quote_label'] }}</button>@endif
                        @if ($d['ask_label'])<a href="{{ route('contact', ['topic' => $t->slug]) }}" class="inline-flex min-h-[2.75rem] items-center text-brand-700 underline underline-offset-4 hover:text-rust">{{ $d['ask_label'] }}</a>@endif
                    </p>
                </article>
            @endforeach
        </x-slider>
    </div>
</section>
@endif
