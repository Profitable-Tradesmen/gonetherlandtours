@if (count($d['items']))
<section class="py-16 sm:py-24" aria-labelledby="st-{{ $sid }}">
    <div class="container-site">
        <x-section-heading data-reveal="up" :eyebrow="$d['eyebrow'] ?: null" :title="$d['heading']" :id="'st-'.$sid" />
        <x-slider tag="ol" class="grid gap-8 md:grid-cols-3">
            @foreach ($d['items'] as $i => $s)
                <li class="text-center" data-reveal="up" style="--d:{{ $i * 150 }}ms">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-brown font-display text-2xl text-accent">{{ $i + 1 }}</span>
                    <h3 class="mt-4 text-xl font-medium">{{ $s['title'] }}</h3>
                    <p class="mx-auto mt-2 max-w-xs text-sm text-ink/75">{{ $s['text'] }}</p>
                </li>
            @endforeach
        </x-slider>
    </div>
</section>
@endif
