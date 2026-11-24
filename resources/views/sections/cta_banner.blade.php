@php
    $ticks = array_values(array_filter([text('quiz_trust_2'), text('quiz_trust_1'), text('quiz_trust_3')]));
    // Placeholder contact details are shown until Admin > Settings has the real ones (same as the top bar and footer).
    $phone = setting('phone') ?: '+1 000-000-0000';
    $email = setting('email') ?: 'info@gonetherlandtours.com';
@endphp
{{-- Closing call to action: a photo with a navy gradient. Left: label, accent heading and text. Right: a frosted card with the reassurances, the quote button
     and the ways to reach a specialist, so both sides have weight. Stacks on phones. --}}
<section class="relative isolate overflow-hidden py-16 text-white sm:py-24 lg:py-28" aria-labelledby="cb-{{ $sid }}">
    @if ($d['image'])<x-picture :src="$d['image']" :alt="$d['image_alt']" :width="2000" :height="1116" class="kenburns absolute inset-0 -z-20 h-full w-full object-cover" />@endif
    <div class="absolute inset-0 -z-10 bg-gradient-to-r from-brand-900/90 via-brand-900/70 to-brand-900/45"></div>
    <div class="container-site grid items-center gap-10 lg:grid-cols-[minmax(0,1.15fr)_minmax(0,1fr)] lg:gap-16">
        <div class="text-center lg:text-left" data-reveal="left">
            @if (! empty($d['eyebrow']))
                <p class="label-caps text-white">{{ $d['eyebrow'] }}</p>
                <span class="mb-4 mt-2 flex justify-center gap-1.5 lg:justify-start" aria-hidden="true"><i class="h-[3px] w-7 bg-white/70"></i><i class="h-[3px] w-7 bg-accent"></i><i class="h-[3px] w-7 bg-white/70"></i></span>
            @endif
            <h2 id="cb-{{ $sid }}" class="text-3xl font-medium leading-tight sm:text-4xl lg:text-5xl">{!! emphasize($d['heading'], 'text-accent') !!}</h2>
            @if ($d['text'])<p class="mx-auto mt-4 max-w-2xl text-base leading-relaxed text-white/90 sm:text-lg lg:mx-0 lg:max-w-xl">{{ $d['text'] }}</p>@endif
        </div>
        <div class="mx-auto w-full max-w-md rounded-2xl border border-white/25 bg-white/10 p-6 shadow-card backdrop-blur-md sm:p-8 lg:mx-0 lg:max-w-none" data-reveal="right">
            @if ($ticks)
                <ul class="space-y-2.5 text-sm font-semibold sm:text-base">
                    @foreach ($ticks as $t)<li class="flex items-center gap-3"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-accent text-ink"><x-icon name="check" class="h-3.5 w-3.5" /></span>{{ $t }}</li>@endforeach
                </ul>
            @endif
            @if ($d['button_label'])<x-button @click="$store.modal.show()" class="mt-6 w-full justify-center">{{ $d['button_label'] }}</x-button>@endif
            @if (text('quote_promise'))<p class="mt-3 text-center text-sm text-white/80">{{ text('quote_promise') }}</p>@endif
            @if ($d['show_phone'])
                <div class="mt-6 flex flex-wrap items-center justify-center gap-x-6 gap-y-2 border-t border-white/20 pt-5 text-sm">
                    <a href="tel:{{ preg_replace('/[^+\d]/', '', $phone) }}" class="inline-flex min-h-[2.75rem] items-center gap-2 font-semibold hover:text-accent"><x-icon name="phone" class="h-4 w-4 text-accent" />{{ trim($d['phone_label'].' '.$phone) }}</a>
                    <a href="mailto:{{ $email }}" class="inline-flex min-h-[2.75rem] items-center gap-2 font-semibold hover:text-accent"><x-icon name="mail" class="h-4 w-4 text-accent" />{{ $email }}</a>
                </div>
            @endif
        </div>
    </div>
</section>
