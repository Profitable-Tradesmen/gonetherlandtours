@php
    $cover = $package->images->first();
    $fallback = $package->tourType?->image ?: 'images/tour-self-drive.webp';
    $hasPrice = (bool) $package->price_from;
    $badge = $package->is_sample ? (text('card_sample_badge') ?: null) : null;
    $url = route('packages.show', $package);
    $points = collect($package->highlights ?? [])->map(fn ($h) => is_array($h) ? ($h['text'] ?? $h['title'] ?? '') : (string) $h)->filter()->take(3);
    $nights = $package->nights.' '.\Illuminate\Support\Str::plural('night', $package->nights);
@endphp
{{-- Package card. One markup, two looks (real data only):
     phones (< 640px): a horizontal dark navy card, photo on the left, label, title, price, "More info" link and a quote button on the right;
     640px and up: a white card, photo on top, price block, up to three ticked highlights, Get a quote + Details. --}}
<article class="group flex h-full flex-row overflow-hidden rounded-2xl transition duration-300 sm:hover:-translate-y-1 sm:hover:shadow-[0_22px_40px_-16px_rgb(12_13_17/0.35)] bg-brand-900 text-white shadow-card sm:flex-col sm:bg-white sm:text-ink sm:border sm:border-ink/15">
    <a href="{{ $url }}" class="relative block min-h-[15rem] w-[38%] shrink-0 overflow-hidden sm:min-h-0 sm:w-full" tabindex="-1" aria-hidden="true">
        @if ($cover)
            <x-picture :media="$cover" :alt="$cover->alt" class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-105 sm:static sm:aspect-[3/2] sm:h-auto" sizes="(min-width:1024px) 33vw, (min-width:640px) 50vw, 38vw" />
        @else
            <x-picture :src="$fallback" :alt="$package->tourType?->image_alt ?? ''" :width="960" :height="640" class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-105 sm:static sm:aspect-[3/2] sm:h-auto" sizes="(min-width:1024px) 33vw, (min-width:640px) 50vw, 38vw" />
        @endif
        @if ($compact ?? false)
            <span class="pointer-events-none absolute inset-x-0 bottom-0 hidden h-1/2 bg-gradient-to-t from-ink/65 to-transparent sm:block"></span>
            <span class="label-caps absolute left-3 top-3 hidden rounded-full bg-white/95 px-3 py-1.5 text-xs font-bold text-brand-900 shadow-sm sm:inline-flex sm:items-center sm:gap-1.5"><x-icon name="clock" class="h-3.5 w-3.5 text-rust" />{{ $nights }}</span>
            @if ($hasPrice)<span class="absolute bottom-3 right-3 hidden rounded-xl bg-accent px-3.5 py-2 text-right text-ink shadow-card sm:block"><span class="label-caps block text-[0.7rem] leading-none text-ink/70">{{ text('card_from_label') ?: 'From' }}</span><span class="block font-display text-2xl font-medium leading-tight">{{ $package->priceLabel() }}@if (text('card_per_person'))<span class="ml-1 font-sans text-xs font-semibold text-ink/70">{{ text('card_per_person') }}</span>@endif</span></span>@endif
        @endif
        @if ($badge)
            <span class="label-caps absolute left-0 top-4 whitespace-nowrap bg-accent px-3 py-1.5 text-xs leading-tight text-ink sm:left-auto sm:right-0 sm:px-4 sm:text-xs">{{ $badge }}</span>
        @endif
    </a>

    <div class="flex min-w-0 flex-1 flex-col p-4 sm:p-6">
        {{-- phones: one small accent label; larger screens: chips --}}
        <p class="label-caps text-xs text-accent sm:hidden">{{ $nights }}@if ($package->tourType) · {{ $package->tourType->name }}@endif</p>
        @if ($compact ?? false)
            @if ($package->tourType)<p class="label-caps hidden items-center gap-2 text-xs font-bold text-rust sm:flex"><x-icon :name="$package->tourType->icon ?: 'check'" class="h-4 w-4" />{{ $package->tourType->name }}</p>@endif
        @endif
        <div class="{{ ($compact ?? false) ? 'sm:hidden' : '' }} hidden flex-wrap items-center gap-2 text-xs font-bold uppercase tracking-wider sm:flex">
            <span class="rounded-full bg-accent px-2.5 py-1 text-ink">{{ $nights }}</span>
            @if ($package->tourType)<span class="rounded-full bg-sand px-2.5 py-1 text-brand-900">{{ $package->tourType->name }}</span>@endif
            @if ($package->destination && ! ($compact ?? false))<span class="rounded-full border border-brand-700/40 px-2.5 py-1 text-brand-900">{{ $package->destination->name }}</span>@endif
        </div>
        <h{{ $headingLevel ?? 3 }} class="mt-2 text-lg font-medium leading-snug sm:mt-3 sm:line-clamp-2 {{ ($compact ?? false) ? 'sm:min-h-[4rem] sm:text-[1.45rem]' : 'sm:text-2xl' }}"><a href="{{ $url }}" class="hover:text-accent sm:hover:text-rust">{{ $package->title }}</a></h{{ $headingLevel ?? 3 }}>
        @unless ($compact ?? false)<p class="mt-2 hidden text-sm leading-relaxed text-ink/75 sm:mb-4 sm:line-clamp-3 sm:block">{{ $package->summary }}</p>@endunless

        <div class="mt-3 {{ ($compact ?? false) ? 'sm:hidden' : 'sm:mt-auto sm:pt-4' }} {{ ($compact ?? false) ? '' : 'sm:border-t sm:border-sand-200' }}">
            <p class="label-caps text-xs text-white/60 sm:text-ink/60">{{ text('card_from_label') ?: 'From' }}</p>
            @if ($hasPrice)
                <p class="font-display text-xl font-medium text-accent sm:text-3xl sm:text-brand-900">{{ $package->priceLabel() }}@if (text('card_per_person'))<span class="ml-1 text-xs font-semibold text-white/60 sm:text-ink/60">{{ text('card_per_person') }}</span>@endif</p>
            @else
                <p class="font-display text-lg font-medium text-accent sm:text-xl sm:text-brand-900">{{ $package->priceLabel() }}</p>
            @endif
        </div>

        @if ($points->isNotEmpty() && ! ($compact ?? false))
            <ul class="mt-4 hidden space-y-1.5 text-sm sm:block">
                @foreach ($points as $pt)<li class="flex items-start gap-2"><x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0 text-brand-700" /><span>{{ $pt }}</span></li>@endforeach
            </ul>
        @endif

        <div class="mt-auto flex flex-col items-start gap-3 pt-4 sm:grid sm:grid-cols-2 sm:pt-5">
            <a href="{{ $url }}" class="label-caps min-h-[2.75rem] inline-flex items-center text-xs underline underline-offset-4 hover:text-accent sm:hidden">{{ text('card_more_info_label') ?: 'More info' }}<span class="sr-only"> about {{ $package->title }}</span></a>
            <button type="button" class="btn btn-primary w-full !px-3" @click="$store.modal.show({ id: {{ $package->id }}, name: @js($package->title) })">{{ text('card_quote_label') ?: 'Get a quote' }}</button>
            <a href="{{ $url }}" class="btn btn-outline hidden w-full !px-3 text-brand-900 sm:inline-flex">{{ text('card_details_label') ?: 'Details' }}<span class="sr-only"> about {{ $package->title }}</span></a>
        </div>
    </div>
</article>
