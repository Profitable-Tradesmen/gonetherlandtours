{{-- Photo band. Without a heading it is just a wide photo. With text it is a split band in the site's colors: a sand panel on the left (small label,
     big heading, one line, up to three icon points, gold button, a faint windmill drawing behind) and the photo on the right with a gold edge and the
     place name; on phones the photo sits on top of the text. --}}
@if (! empty($d['image']))
@php $hasText = ! empty($d['heading']); $points = array_values(array_filter($d['points'] ?? [], fn ($p) => ! empty($p['text']))); @endphp
@if ($hasText)
<section class="bg-white" style="padding-block:0" aria-label="{{ $d['eyebrow'] ?? '' }}">
    <div>
        {{-- A soft rounded card: cream text panel on the left, rounded photo with a thin white inner frame and a place pill on the right. --}}
        <div class="relative isolate grid overflow-hidden border-y border-accent/30 bg-gradient-to-br from-white via-sand/60 to-sand lg:bg-none lg:bg-[#f7f1ea] lg:min-h-[32rem] lg:grid-cols-[minmax(0,5fr)_minmax(0,6fr)]">
            <div class="relative z-10 order-2 flex items-center overflow-hidden px-6 py-12 sm:px-10 lg:order-1 lg:justify-end lg:py-16 lg:pl-[max(2rem,calc((100vw-80rem)/2+1rem))] lg:pr-14">
                <div class="pointer-events-none absolute -bottom-4 left-2 -z-10 h-40 w-40 origin-bottom-left scale-[1.6]" aria-hidden="true">@include('partials.place-icon-bg', ['slug' => 'tulips-and-countryside'])</div>
                <div class="mx-auto w-full max-w-md text-center lg:mx-0 lg:text-left" data-reveal="up">
                    @if (! empty($d['eyebrow']))
                        <p class="label-caps text-brand-900">{{ $d['eyebrow'] }}</p>
                        <span class="mb-5 mt-3 flex items-center justify-center gap-2 lg:justify-start" aria-hidden="true"><i class="h-0.5 w-10 bg-brand-900"></i><i class="h-1.5 w-1.5 rotate-45 bg-rust"></i><i class="h-0.5 w-10 bg-brand-900"></i></span>
                    @endif
                    <p class="font-display text-4xl font-medium leading-[1.08] text-brand-900 sm:text-5xl [&_em]:block">{!! emphasize($d['heading'], 'text-rust') !!}</p>
                    @if (! empty($d['text']))<p class="mt-5 text-base leading-relaxed text-ink/75 sm:text-lg">{{ $d['text'] }}</p>@endif
                    @if (! empty($d['button_label']))
                        <div class="mt-7 flex flex-wrap items-center justify-center gap-x-5 gap-y-3 lg:justify-start">
                            <x-button @click="$store.modal.show()">{{ $d['button_label'] }} <x-icon name="arrow" class="h-4 w-4" /></x-button>
                            <span class="hidden h-8 w-px bg-rust/40 sm:block" aria-hidden="true"></span>
                            <span class="inline-flex items-center gap-1.5 text-sm text-ink/75"><x-icon name="check" class="h-4 w-4 text-rust" />Free, no obligation</span>
                        </div>
                    @endif
                </div>
            </div>
            <div class="relative order-1 h-64 sm:h-80 lg:order-2 lg:-ml-28 lg:h-auto lg:p-0 lg:pl-0">
                <div class="relative h-full overflow-hidden">
                    <x-picture :src="$d['image']" :alt="$d['image_alt'] ?? ''" :width="2000" :height="1116" sizes="(min-width:1024px) 55vw, 100vw" class="absolute inset-0 h-full w-full object-cover" />
                    <span class="pointer-events-none absolute inset-3 rounded-[1.1rem] border border-white/60 lg:hidden" aria-hidden="true"></span>
                    {{-- Wide screens: the photo melts into the cream panel along a flowing S-curve. --}}
                    <svg class="pointer-events-none absolute inset-0 hidden h-full w-full overflow-visible lg:block" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true" focusable="false">
                        <defs><filter id="pbblur" x="-200%" y="-50%" width="400%" height="200%"><feGaussianBlur stdDeviation="2.6 0.9"/></filter></defs>
                        <path d="M-60 -60H21C8 12 3 26 9 42C15 58 8 76 21 100V160H-60Z" fill="#f7f1ea" filter="url(#pbblur)"/>
                    </svg>
                    @if (! empty($d['caption']))<span class="absolute bottom-6 right-6 inline-flex items-center gap-2 rounded-full bg-white px-4 py-2.5 text-xs font-semibold text-ink shadow-card sm:bottom-8 sm:right-8"><x-icon name="map" class="h-4 w-4 text-rust" />{{ $d['caption'] }}</span>@endif
                </div>
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
