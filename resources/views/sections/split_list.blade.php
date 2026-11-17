<section class="py-16 sm:py-24" aria-labelledby="sl-{{ $sid }}">
    <div class="container-site grid items-center gap-10 lg:grid-cols-2">
        <div class="relative grid grid-cols-2 gap-4">
            @if ($d['image1'])<x-picture :src="$d['image1']" :alt="$d['image1_alt']" :width="900" :height="1100" class="aspect-[4/5] w-full rounded-2xl object-cover shadow-card" data-reveal="left" />@endif
            @if ($d['image2'])<x-picture :src="$d['image2']" :alt="$d['image2_alt']" :width="900" :height="1100" class="mt-10 aspect-[4/5] w-full rounded-2xl object-cover shadow-card" data-reveal="zoom" style="--d:150ms" />@endif
            @if ($d['badge'])<div class="float-slow absolute bottom-4 left-4 rounded-xl bg-accent px-4 py-3 text-sm font-bold text-ink shadow-card">{{ $d['badge'] }}</div>@endif
        </div>
        <div data-reveal="right">
            @if ($d['eyebrow'])<p class="label-caps text-rust">{{ $d['eyebrow'] }}</p>@endif
            <h2 id="sl-{{ $sid }}" class="mt-3 text-3xl font-medium sm:text-4xl lg:text-5xl">{!! emphasize($d['heading'], 'text-rust') !!}</h2>
            <ol class="mt-8 space-y-6">
                @foreach ($d['items'] as $i => $w)
                    <li class="flex gap-4" data-reveal="up" style="--d:{{ $i * 120 }}ms">
                        <span class="font-display text-3xl text-accent-600">{{ sprintf('%02d', $i + 1) }}</span>
                        <div><h3 class="text-xl font-medium">{{ $w['title'] }}</h3><p class="mt-1 text-sm leading-relaxed text-ink/75">{{ $w['text'] }}</p></div>
                    </li>
                @endforeach
            </ol>
            @if ($d['button_label'])<p class="mt-8"><x-button @click="$store.modal.show()">{{ $d['button_label'] }}</x-button></p>@endif
        </div>
    </div>
</section>
