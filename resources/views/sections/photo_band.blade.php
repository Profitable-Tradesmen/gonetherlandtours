{{-- Photo band. Without a heading it is just a wide photo. With text it is a split band in the site's colors: a sand panel on the left (small label,
     big heading, one line, up to three icon points, gold button, a faint windmill drawing behind) and the photo on the right with a gold edge and the
     place name; on phones the photo sits on top of the text. --}}
@if (! empty($d['image']))
@php $hasText = ! empty($d['heading']); $points = array_values(array_filter($d['points'] ?? [], fn ($p) => ! empty($p['text']))); @endphp
@if ($hasText)
<section class="bg-sand" style="padding-block:0" aria-label="{{ $d['eyebrow'] ?? '' }}">
    <div class="grid lg:min-h-[34rem] lg:grid-cols-[minmax(0,5fr)_minmax(0,7fr)]">
        <div class="relative isolate order-2 flex items-center overflow-hidden px-6 py-14 sm:px-10 lg:order-1 lg:justify-end lg:py-20 lg:pl-[max(2rem,calc((100vw-80rem)/2+1rem))] lg:pr-14">
            {{-- faint windmill drawing, large, behind the text --}}
            <div class="pointer-events-none absolute -bottom-6 -left-8 -z-10 h-40 w-40 origin-bottom-left scale-[2.2]" aria-hidden="true">@include('partials.place-icon-bg', ['slug' => 'tulips-and-countryside'])</div>
            <div class="mx-auto w-full max-w-md text-center lg:mx-0 lg:max-w-lg lg:text-left" data-reveal="up">
                @if (! empty($d['eyebrow']))
                    <p class="label-caps text-rust">{{ $d['eyebrow'] }}</p>
                    <span class="mb-4 mt-3 flex items-center justify-center gap-2 lg:justify-start" aria-hidden="true"><i class="h-0.5 w-9 bg-brand-900"></i><i class="h-1.5 w-1.5 rotate-45 bg-rust"></i><i class="h-0.5 w-9 bg-brand-900"></i></span>
                @endif
                <p class="font-display text-4xl font-medium leading-[1.1] text-brand-900 sm:text-[2.6rem]">{!! emphasize($d['heading'], 'text-rust') !!}</p>
                @if (! empty($d['text']))<p class="mt-5 text-base leading-relaxed text-ink/75 sm:text-lg">{{ $d['text'] }}</p>@endif
                @if ($points)
                    <ul class="mt-6 space-y-3 text-left">
                        @foreach ($points as $p)
                            <li class="flex items-center gap-3 text-sm font-semibold text-ink sm:text-base"><span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-900 text-accent"><x-icon :name="$p['icon'] ?: 'check'" class="h-4 w-4" /></span>{{ $p['text'] }}</li>
                        @endforeach
                    </ul>
                @endif
                @if (! empty($d['button_label']))
                    <div class="mt-8 flex flex-wrap items-center justify-center gap-x-5 gap-y-3 lg:justify-start">
                        <x-button @click="$store.modal.show()">{{ $d['button_label'] }} <x-icon name="arrow" class="h-4 w-4" /></x-button>
                        <span class="inline-flex items-center gap-1.5 text-sm text-ink/70"><x-icon name="check" class="h-4 w-4 text-rust" />Free, no obligation</span>
                    </div>
                @endif
            </div>
        </div>
        <div class="relative order-1 h-64 border-b-4 border-accent sm:h-80 lg:order-2 lg:h-auto lg:border-b-0 lg:border-l-4">
            <x-picture :src="$d['image']" :alt="$d['image_alt'] ?? ''" :width="2000" :height="1116" sizes="(min-width:1024px) 58vw, 100vw" class="absolute inset-0 h-full w-full object-cover" />
            @if (! empty($d['caption']))<span class="absolute bottom-5 right-5 inline-flex items-center gap-2 rounded-full bg-white/90 px-4 py-2 text-xs font-semibold text-ink shadow-card backdrop-blur-sm sm:bottom-6 sm:right-6"><x-icon name="map" class="h-4 w-4 text-rust" />{{ $d['caption'] }}</span>@endif
        </div>
    </div>
</section>
@else
<section class="relative overflow-hidden bg-brand-900" style="padding-block:0" @if (! empty($d['image_alt'])) role="img" aria-label="{{ $d['image_alt'] }}" @else aria-hidden="true" @endif>
    <x-picture :src="$d['image']" :alt="''" :width="2000" :height="1116" sizes="100vw" class="block h-56 w-full object-cover sm:h-80 lg:h-[26rem]" />
</section>
@endif
@endif
