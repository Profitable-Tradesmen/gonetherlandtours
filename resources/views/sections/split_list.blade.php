<section class="hidden bg-sand py-16 sm:block sm:py-24 lg:py-32" aria-labelledby="sl-{{ $sid }}">
    <div class="container-site grid items-center gap-10 lg:grid-cols-2 lg:gap-24 xl:gap-28">
        {{-- One large photo; the badge sits on it as a dark tile and an optional second photo overlaps its corner, tilted (it straightens on hover) inside a wrapper
             so the reveal animation does not fight the rotation. Depth comes from shadows only: no borders. --}}
        <div class="relative lg:mr-4">
            @if ($d['image1'])<x-picture :src="$d['image1']" :alt="$d['image1_alt']" :width="900" :height="1100" class="relative aspect-[4/5] w-full rounded-2xl object-cover shadow-card" data-reveal="left" />@endif
            @if ($d['image2'])<div class="absolute -bottom-8 right-4 hidden w-2/5 rotate-[5deg] transition-transform duration-500 hover:rotate-0 sm:block lg:-right-8"><x-picture :src="$d['image2']" :alt="$d['image2_alt']" :width="900" :height="1100" class="aspect-[4/5] w-full rounded-2xl object-cover shadow-[0_24px_44px_-10px_rgb(12_13_17/0.6)]" data-reveal="zoom" style="--d:150ms" /></div>@endif
            @if ($d['badge'])<div class="absolute bottom-0 left-0 z-10 rounded-tr-2xl rounded-bl-2xl bg-brand-900/95 px-5 py-4 text-sm font-semibold text-white shadow-card"><span class="mr-2 text-accent">&#9733;</span>{{ $d['badge'] }}</div>@endif
        </div>
        <div data-reveal="right" class="lg:pl-4">
            @if ($d['eyebrow'])
                <p class="label-caps text-ink">{{ $d['eyebrow'] }}</p>
                <span class="mb-4 mt-3 flex items-center gap-2" aria-hidden="true"><i class="h-0.5 w-9 bg-brand-900"></i><i class="h-1.5 w-1.5 rotate-45 bg-rust"></i><i class="h-0.5 w-9 bg-brand-900"></i></span>
            @endif
            <h2 id="sl-{{ $sid }}" class="text-3xl font-medium sm:text-4xl lg:text-5xl">{!! emphasize($d['heading'], 'text-rust') !!}</h2>
            <ul class="mt-10 space-y-7">
                @foreach ($d['items'] as $i => $w)
                    <li class="flex gap-3" data-reveal="up" style="--d:{{ $i * 120 }}ms">
                        <x-icon name="check" class="mt-1 h-5 w-5 shrink-0 text-brand-700" />
                        <div><h3 class="font-sans text-lg font-bold">{{ $w['title'] }}</h3><p class="mt-1 text-sm leading-relaxed text-ink/75">{{ $w['text'] }}</p></div>
                    </li>
                @endforeach
            </ul>
            @if ($d['button_label'])<p class="mt-12"><x-button @click="$store.modal.show()">{{ $d['button_label'] }}</x-button></p>@endif
        </div>
    </div>
</section>
