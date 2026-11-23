@php $galItems = array_values(array_filter($d['items'], fn ($g) => ! empty($g['image']))); @endphp
@if (count($galItems))
<section class="py-16 sm:py-24" aria-labelledby="ga-{{ $sid }}" x-data="{ open: null }" @keydown.escape.window="open = null">
    <div class="container-site">
        <x-section-heading data-reveal="up" :eyebrow="$d['eyebrow'] ?: null" :title="$d['heading']" :id="'ga-'.$sid" />
        {{-- Balanced mosaic: 4 columns, pattern repeats every 6 photos. Row 1 = wide + 2 small, row 2 = 2 small + wide. --}}
        <div class="gallery-grid grid auto-rows-[11rem] grid-cols-2 gap-3 sm:auto-rows-[14rem] md:auto-rows-[17rem] md:grid-cols-4 md:gap-4">
            @foreach ($galItems as $i => $g)
                <button type="button" class="gallery-tile group relative isolate overflow-hidden rounded-2xl text-left shadow-card {{ in_array($i % 6, [0, 5]) ? 'col-span-2' : '' }}" data-reveal="zoom" style="--d:{{ ($i % 6) * 90 }}ms" @click="open = {{ $i }}" aria-label="Enlarge: {{ $g['caption'] }}">
                    <x-picture :src="$g['image']" :alt="$g['image_alt']" :width="2000" :height="1116" class="absolute inset-0 -z-10 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" />
                    <span class="absolute inset-0 bg-gradient-to-t from-ink/90 via-ink/30 to-transparent opacity-95 transition-opacity group-hover:opacity-100"></span>
                    <span class="absolute inset-x-0 bottom-0 flex items-end justify-between gap-2 p-4">
                        <span><span class="label-caps mb-1 inline-block rounded bg-ink/75 px-2 py-0.5 text-accent">{{ sprintf('%02d', $i + 1) }}</span><br><span class="font-display text-lg leading-tight text-white sm:text-xl">{{ $g['caption'] }}</span></span>
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white/90 text-ink opacity-0 transition-opacity group-hover:opacity-100 group-focus-visible:opacity-100" aria-hidden="true"><svg viewBox="0 0 24 24" class="h-4 w-4 fill-none stroke-current" stroke-width="2"><path d="M4 9V4h5M20 9V4h-5M4 15v5h5M20 15v5h-5"/></svg></span>
                    </span>
                </button>
            @endforeach
        </div>
        <div x-show="open !== null" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center bg-ink/90 p-4" @click="open = null" role="dialog" aria-modal="true" aria-label="Photo viewer">
            @foreach ($galItems as $i => $g)
                <figure x-show="open === {{ $i }}" class="max-w-4xl" @click.stop>
                    <img src="{{ asset($g['image']) }}" loading="lazy" decoding="async" alt="{{ $g['image_alt'] }}" class="max-h-[80vh] w-full rounded-xl object-contain">
                    <figcaption class="mt-3 text-center text-sm text-white">{{ $g['caption'] }}</figcaption>
                </figure>
            @endforeach
            <button type="button" class="btn btn-primary absolute right-4 top-4 !py-2" @click="open = null">Close</button>
        </div>
        @if ($d['closing_heading'] || $d['button_label'])
        <div class="mt-12 text-center" data-reveal="up">
            @if ($d['closing_heading'])<p class="font-display text-2xl">{{ $d['closing_heading'] }}</p>@endif
            @if ($d['button_label'])<p class="mt-4"><x-button @click="$store.modal.show()">{{ $d['button_label'] }}</x-button></p>@endif
        </div>
        @endif
    </div>
</section>
@endif
