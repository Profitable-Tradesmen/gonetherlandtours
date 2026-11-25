{{-- Full-width photo band: a breather between two sections. Optional text on the photo (small label, heading, one line and a button). --}}
@if (! empty($d['image']))
@php $hasText = ! empty($d['heading']); @endphp
<section class="relative isolate flex h-72 items-center justify-center overflow-hidden bg-brand-900 text-white sm:h-96 lg:h-[28rem]" style="padding-block:0" @if (! $hasText && ! empty($d['image_alt'])) role="img" aria-label="{{ $d['image_alt'] }}" @endif>
    <x-picture :src="$d['image']" :alt="$hasText ? ($d['image_alt'] ?? '') : ''" :width="2000" :height="1116" sizes="100vw" class="absolute inset-0 -z-20 h-full w-full object-cover" />
    @if ($hasText)
        <span class="absolute inset-0 -z-10 bg-gradient-to-t from-ink/80 via-ink/55 to-ink/45" aria-hidden="true"></span>
        <div class="container-site text-center" data-reveal="up">
            @if (! empty($d['eyebrow']))<p class="label-caps text-accent">{{ $d['eyebrow'] }}</p>@endif
            <p class="mx-auto mt-3 max-w-3xl font-display text-3xl font-medium leading-tight [text-shadow:0_2px_16px_rgb(0_0_0/0.45)] sm:text-4xl lg:text-5xl">{!! emphasize($d['heading'], 'text-accent') !!}</p>
            @if (! empty($d['text']))<p class="mx-auto mt-4 max-w-xl text-base text-white/90 sm:text-lg">{{ $d['text'] }}</p>@endif
            @if (! empty($d['button_label']))<p class="mt-6"><x-button @click="$store.modal.show()">{{ $d['button_label'] }} <x-icon name="arrow" class="h-4 w-4" /></x-button></p>@endif
        </div>
    @endif
</section>
@endif
