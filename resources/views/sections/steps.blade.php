@php
    $count = count($d['items']);
    $timeline = $d['style'] === 'timeline';
    $tiles = $d['style'] === 'tiles';
    $cols = $count <= 3 ? 'md:grid-cols-3' : 'md:grid-cols-4';
    $perRow = $count <= 3 ? max($count, 1) : 4;
@endphp
@if ($count)
<section class="py-16 sm:py-24" aria-labelledby="st-{{ $sid }}">
    <div class="container-site">
        <x-section-heading data-reveal="up" :eyebrow="$d['eyebrow'] ?: null" :title="$d['heading']" :subtitle="($d['intro'] ?? '') ?: null" :id="'st-'.$sid" />
        @if ($tiles)
            {{-- Dark numbered tiles: a white corner tab with the number, then title and text; a swipe row on phones. --}}
            <x-slider tag="ol" class="grid gap-6 md:grid-cols-3 lg:gap-8">
                @foreach ($d['items'] as $i => $s)
                    <li class="relative overflow-hidden rounded-xl bg-brand-900 px-6 pb-10 pt-14 text-center text-white shadow-card" data-reveal="up" style="--d:{{ $i * 150 }}ms">
                        <span class="absolute left-0 top-0 flex h-16 w-16 items-start justify-start rounded-br-full bg-white pl-5 pt-3 font-display text-3xl font-medium text-brand-900" aria-hidden="true">{{ $i + 1 }}</span>
                        <h3 class="mt-6 text-2xl font-medium">{{ $s['title'] }}</h3>
                        <p class="mx-auto mt-3 max-w-xs text-sm leading-relaxed text-white/85">{{ $s['text'] }}</p>
                    </li>
                @endforeach
            </x-slider>
        @elseif ($timeline)
            {{-- Connected timeline: a dashed line links the numbered circles on desktop, a vertical line on phones. --}}
            <ol class="relative grid gap-9 {{ $cols }}">
                @if ($count > 1 && $count <= 4)
                    <span class="pointer-events-none absolute top-7 hidden border-t-2 border-dashed border-accent md:block" style="left: calc(100% / {{ $perRow * 2 }}); right: calc(100% / {{ $perRow * 2 }});" aria-hidden="true"></span>
                @endif
                @foreach ($d['items'] as $i => $s)
                    <li class="relative flex gap-5 md:block md:text-center" data-reveal="up" style="--d:{{ $i * 150 }}ms">
                        @unless ($loop->last)<span class="absolute bottom-[-2.25rem] left-7 top-14 w-px -translate-x-1/2 border-l-2 border-dashed border-accent md:hidden" aria-hidden="true"></span>@endunless
                        <span class="relative z-10 flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-brown font-display text-2xl text-accent ring-4 ring-white md:mx-auto">{{ $i + 1 }}</span>
                        <div>
                            <h3 class="text-xl font-medium md:mt-4">{{ $s['title'] }}</h3>
                            <p class="mt-1.5 text-sm text-ink/75 md:mx-auto md:mt-2 md:max-w-xs">{{ $s['text'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        @else
            <x-slider tag="ol" class="grid gap-8 md:grid-cols-3">
                @foreach ($d['items'] as $i => $s)
                    <li class="text-center" data-reveal="up" style="--d:{{ $i * 150 }}ms">
                        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-brown font-display text-2xl text-accent">{{ $i + 1 }}</span>
                        <h3 class="mt-4 text-xl font-medium">{{ $s['title'] }}</h3>
                        <p class="mx-auto mt-2 max-w-xs text-sm text-ink/75">{{ $s['text'] }}</p>
                    </li>
                @endforeach
            </x-slider>
        @endif
        @if ($d['button_label'] ?? '')<p class="mt-10 text-center"><x-button @click="$store.modal.show()">{{ $d['button_label'] }}</x-button></p>@endif
    </div>
</section>
@endif
