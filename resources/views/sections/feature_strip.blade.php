@if (count($d['items']))
{{-- Dark band right under the hero: up to four centered points, each with a round outlined icon, a title and one line. --}}
<section class="bg-brand-900 text-white" aria-label="Why choose us">
    <div class="container-site grid sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($d['items'] as $i => $it)
            <div class="px-6 py-9 text-center {{ $i > 0 ? 'lg:border-l lg:border-white/15' : '' }} {{ $i % 2 === 1 ? 'sm:border-l sm:border-white/15 lg:border-l' : '' }}" data-reveal="up" style="--d:{{ $i * 80 }}ms">
                <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full border-2 {{ $i % 2 === 0 ? 'border-accent text-accent' : 'border-white/60 text-white' }}"><x-icon :name="$it['icon'] ?: 'check'" class="h-7 w-7" /></span>
                <h3 class="mt-5 font-display text-xl font-medium">{{ $it['title'] }}</h3>
                <p class="label-caps mt-2 text-[0.68rem] leading-relaxed text-white/80">{{ $it['text'] }}</p>
            </div>
        @endforeach
    </div>
</section>
@endif
