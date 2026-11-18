<section class="bg-sand py-16 sm:py-24" aria-labelledby="sl-{{ $sid }}">
    <div class="container-site grid items-center gap-10 lg:grid-cols-2 lg:gap-14">
        {{-- One large photo; the badge sits on it as a dark tile and an optional second photo overlaps its corner. --}}
        <div class="relative">
            @if ($d['image1'])<x-picture :src="$d['image1']" :alt="$d['image1_alt']" :width="900" :height="1100" class="aspect-[4/5] w-full rounded-xl object-cover shadow-card" data-reveal="left" />@endif
            @if ($d['image2'])<x-picture :src="$d['image2']" :alt="$d['image2_alt']" :width="900" :height="1100" class="absolute -bottom-6 -right-3 hidden aspect-[4/5] w-2/5 rounded-xl border-4 border-sand object-cover shadow-card sm:block lg:-right-6" data-reveal="zoom" style="--d:150ms" />@endif
            @if ($d['badge'])<div class="absolute bottom-0 left-0 rounded-tr-xl bg-brand-900/95 px-5 py-4 text-sm font-semibold text-white shadow-card"><span class="mr-2 text-accent">&#9733;</span>{{ $d['badge'] }}</div>@endif
        </div>
        <div data-reveal="right">
            @if ($d['eyebrow'])<p class="label-caps inline-flex items-center gap-3 text-brand-700"><span class="h-px w-7 bg-brand-700" aria-hidden="true"></span><span class="h-2 w-2 rounded-full bg-rust" aria-hidden="true"></span>{{ $d['eyebrow'] }}</p>@endif
            <h2 id="sl-{{ $sid }}" class="mt-3 text-3xl font-medium sm:text-4xl lg:text-5xl">{!! emphasize($d['heading'], 'text-brand-700') !!}</h2>
            <ul class="mt-8 space-y-5">
                @foreach ($d['items'] as $i => $w)
                    <li class="flex gap-3" data-reveal="up" style="--d:{{ $i * 120 }}ms">
                        <x-icon name="check" class="mt-1 h-5 w-5 shrink-0 text-brand-700" />
                        <div><h3 class="font-sans text-lg font-bold">{{ $w['title'] }}</h3><p class="mt-1 text-sm leading-relaxed text-ink/75">{{ $w['text'] }}</p></div>
                    </li>
                @endforeach
            </ul>
            @if ($d['button_label'])<p class="mt-8"><x-button @click="$store.modal.show()">{{ $d['button_label'] }}</x-button></p>@endif
        </div>
    </div>
</section>
