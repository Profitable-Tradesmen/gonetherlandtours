{{-- Photo band. Without a heading it is just a wide photo. With text it is two columns on a navy background with soft light and a faint skyline: the text on the left
     (small label, big heading, one line, gold button) and on the right a collage of overlapping
     postcards with a gold stamp. On phones the photo sits on top of the text. --}}
@if (! empty($d['image']))
@php $hasText = ! empty($d['heading']); @endphp
@if ($hasText)
<section class="relative isolate overflow-hidden bg-brand-900 pb-20 pt-14 text-white sm:pb-28 sm:pt-20 lg:pb-32 lg:pt-24" aria-label="{{ $d['eyebrow'] ?? '' }}">
    {{-- Background layers, all decorative: soft light behind the postcards, a warm glow and a darker corner, a dashed travel route
         and a faint skyline along the bottom. --}}
    <div class="pointer-events-none absolute inset-0 -z-20" style="background-image:radial-gradient(ellipse 55% 70% at 76% 42%, rgb(255 255 255 / .13), transparent 70%), radial-gradient(ellipse 45% 55% at 100% 0%, color-mix(in srgb, var(--color-accent) 22%, transparent), transparent 70%), radial-gradient(ellipse 70% 80% at 0% 100%, rgb(12 13 17 / .45), transparent 70%)" aria-hidden="true"></div>
    <svg class="pointer-events-none absolute inset-0 -z-10 hidden h-full w-full text-accent opacity-40 lg:block" viewBox="0 0 1440 640" preserveAspectRatio="none" fill="none" aria-hidden="true" focusable="false">
        <path d="M-20 70C170 20 330 120 520 84S800 10 960 92s330 90 500 18" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-dasharray="2 10" vector-effect="non-scaling-stroke"/>
    </svg>
    @include('partials.skyline-silhouette', ['class' => 'pointer-events-none absolute inset-x-0 bottom-0 -z-10 block h-16 w-full text-white opacity-[0.07] sm:h-24 lg:h-28'])
    <div class="container-site grid items-center gap-12 lg:grid-cols-[minmax(0,5fr)_minmax(0,6fr)] lg:gap-16">
        <div class="order-2 mx-auto w-full max-w-lg text-center lg:order-1 lg:mx-0 lg:text-left" data-reveal="left">
            @if (! empty($d['eyebrow']))
                <p class="label-caps text-white">{{ $d['eyebrow'] }}</p>
                <span class="mb-5 mt-3 flex items-center justify-center gap-2 lg:justify-start" aria-hidden="true"><i class="h-0.5 w-10 bg-accent"></i><i class="h-1.5 w-1.5 rotate-45 bg-accent"></i><i class="h-0.5 w-10 bg-accent"></i></span>
            @endif
            <p class="font-display text-4xl font-medium leading-[1.08] sm:text-5xl xl:text-6xl [&_em]:block">{!! emphasize($d['heading'], 'text-accent') !!}</p>
            @if (! empty($d['text']))<p class="mt-5 text-base leading-relaxed text-white/90 sm:text-lg">{{ $d['text'] }}</p>@endif
            @if (! empty($d['button_label']))
                <div class="mt-8 flex flex-wrap items-center justify-center gap-x-5 gap-y-3 lg:justify-start">
                    <x-button @click="$store.modal.show()">{{ $d['button_label'] }} <x-icon name="arrow" class="h-4 w-4" /></x-button>
                    <span class="hidden h-8 w-px bg-white/30 sm:block" aria-hidden="true"></span>
                    <span class="inline-flex items-center gap-1.5 text-sm text-white/90"><x-icon name="check" class="h-4 w-4 text-accent" />Free, no obligation</span>
                </div>
            @endif
        </div>
        {{-- A collage of overlapping postcards, drawn with CSS only (no script). Up to three photos; without "photos" the single
             photo is shown as one postcard. The higher cards lie on top, so every caption stays readable. --}}
        @php
            $cards = array_values(array_filter($d['photos'] ?? [], fn ($c) => ! empty($c['image'])));
            if (! $cards) {
                [$place, $region] = array_pad(array_map('trim', explode(',', (string) ($d['caption'] ?? ''), 2)), 2, '');
                $cards = [['image' => $d['image'], 'image_alt' => $d['image_alt'] ?? '', 'title' => $place, 'place' => $region]];
            }
            $cards = array_slice($cards, 0, 3);
        @endphp
        <div class="order-1 mx-auto w-full max-w-2xl lg:order-2" data-reveal="right">
            <div class="collage collage-{{ count($cards) }}">
                @foreach ($cards as $c)
                    <figure class="collage-card rounded-md bg-white p-1.5 text-ink shadow-[0_26px_45px_-18px_rgb(0_0_0/0.75)] sm:p-2.5">
                        <x-picture :src="$c['image']" :alt="$c['image_alt'] ?? ''" :width="1600" :height="1067" sizes="(min-width:1024px) 32vw, 62vw" class="aspect-[3/2] h-auto w-full rounded-sm object-cover" />
                        @if (! empty($c['title']) || ! empty($c['place']))
                            <figcaption class="px-1 pb-0.5 pt-1.5 sm:flex sm:flex-wrap sm:items-center sm:justify-between sm:gap-x-3 sm:pt-2.5">
                                <span class="block font-display text-sm font-medium italic leading-tight text-brand-900 sm:text-xl">{{ $c['title'] ?? '' }}</span>
                                @if (! empty($c['place']))<span class="label-caps flex items-center gap-1 text-[0.6rem] font-bold text-rust sm:text-[0.65rem]"><x-icon name="map" class="h-3.5 w-3.5" />{{ $c['place'] }}</span>@endif
                            </figcaption>
                        @endif
                    </figure>
                @endforeach
                <span class="pointer-events-none absolute left-[55%] top-0 z-50 flex h-[4.5rem] w-[4.5rem] rotate-12 items-center justify-center rounded-full bg-accent p-1.5 text-brand-900 shadow-card sm:left-[57%] sm:h-24 sm:w-24" aria-hidden="true">
                    <span class="flex h-full w-full flex-col items-center justify-center rounded-full border-2 border-dashed border-brand-900/60 text-center leading-none">
                        <span class="font-display text-[0.55rem] italic sm:text-[0.8rem]">Hello from</span>
                        <span class="mt-1 font-display text-xl font-semibold sm:text-3xl">NL</span>
                    </span>
                </span>
            </div>
        </div>
    </div>
</section>
@else
<section class="relative overflow-hidden bg-brand-900" style="padding-block:0" @if (! empty($d['image_alt'])) role="img" aria-label="{{ $d['image_alt'] }}" @else aria-hidden="true" @endif>
    <x-picture :src="$d['image']" :alt="''" :width="2000" :height="1116" sizes="100vw" class="block h-56 w-full object-cover sm:h-80 lg:h-[26rem]" />
</section>
@endif
@endif
