@php $dests = \App\Models\Destination::active()->orderBy('sort_order')->get(); @endphp
@if ($dests->isNotEmpty())
<section class="{{ ($d['background'] ?? 'sand') === 'white' ? 'bg-white' : 'bg-sand' }} py-16 sm:py-24" aria-labelledby="dc-{{ $sid }}">
    <div class="container-site">
        <x-section-heading data-reveal="up" :eyebrow="$d['eyebrow'] ?: null" :title="$d['heading']" :subtitle="$d['text'] ?: null" :id="'dc-'.$sid" />
        {{-- Phones: swipe carousel with dots (same as the other card sections); from sm: a grid. --}}
        <x-slider class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($dests as $i => $dest)
                <article class="group flex flex-col overflow-hidden rounded-2xl bg-white shadow-card ring-1 ring-sand-200 transition duration-300 hover:-translate-y-1 hover:shadow-xl" data-reveal="up" style="--d:{{ ($i % 3) * 120 }}ms">
                    @if ($dest->image)<x-picture :src="$dest->image" :alt="$dest->image_alt" :width="2000" :height="1116" class="aspect-[4/3] w-full object-cover transition-transform duration-700 group-hover:scale-105" sizes="(min-width:1024px) 33vw, (min-width:640px) 50vw, 100vw" />@endif
                    <div class="flex flex-1 flex-col p-6">
                        <p class="label-caps text-rust">The Netherlands</p>
                        <h3 class="mt-2 text-2xl font-medium">{{ $dest->name }}</h3>
                        <p class="mt-2 flex-1 text-sm leading-relaxed text-ink/75">{{ $dest->summary }}</p>
                        <div class="mt-5 flex flex-wrap gap-3">
                            <x-button :href="route('destinations.show', $dest)">{{ $d['explore_label'] }}</x-button>
                            @if ($d['quote_label'])<x-button variant="outline" @click="$store.modal.show()">{{ $d['quote_label'] }}</x-button>@endif
                        </div>
                    </div>
                </article>
            @endforeach
        </x-slider>
        @if ($d['all_label'])<p class="mt-10 text-center"><x-cta-button variant="secondary" :label="$d['all_label']" :url="$d['all_url']" /></p>@endif
    </div>
</section>
@endif
