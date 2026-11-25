{{-- Mega menu body (Tour types, Destinations, About us): a light panel with a gold top line and three columns, laid out like the sister Go Austria site:
     1. a list of links (title and one line of text, thin dividers) with an optional "view all" link,
     2. one featured card (a trip or the latest journal post),
     3. a navy "Get in touch" card with a quote button and the phone number.
     Expects $eyebrow, $items (href, title, text, new_tab), $foot (label, href) or null, $card (eyebrow, href, media or src, alt, tag, title, meta, cta) or null, $quote (button label). --}}
@php $phone = setting('phone') ?: '+1 000-000-0000'; @endphp
<div class="container-site grid gap-x-10 gap-y-6 pb-9 pt-8 lg:grid-cols-[minmax(0,1.15fr)_minmax(0,1fr)_minmax(0,0.85fr)]">
    <div class="min-w-0">
        <p class="label-caps text-xs font-bold text-rust">{{ $eyebrow }}</p>
        <ul class="mt-4">
            @foreach ($items as $it)
                <li class="border-t border-sand-200 first:border-0">
                    <a href="{{ $it['href'] }}" class="group block py-3.5" @click="dd = null" @if (! empty($it['new_tab'])) target="_blank" rel="noopener" @endif>
                        <span class="flex items-center justify-between gap-3"><span class="font-display text-xl font-medium leading-tight text-brand-900 transition-colors group-hover:text-rust">{{ $it['title'] }}</span><x-icon name="arrow" class="h-4 w-4 shrink-0 text-rust opacity-0 transition group-hover:opacity-100" /></span>
                        @if (! empty($it['text']))<span class="mt-1 block text-sm text-ink/70">{{ $it['text'] }}</span>@endif
                    </a>
                </li>
            @endforeach
        </ul>
        @if (! empty($legal))<div class="mt-3 flex flex-wrap gap-x-5 gap-y-1 border-t border-sand-200 pt-4 text-sm text-ink/70">@foreach ($legal as [$label, $href])<a href="{{ $href }}" class="hover:text-rust" @click="dd = null">{{ $label }}</a>@endforeach</div>@endif
        @if ($foot)<a href="{{ $foot['href'] }}" class="label-caps mt-3 inline-flex items-center gap-2 border-t border-sand-200 pt-4 text-xs font-bold text-brand-700 hover:text-rust" @click="dd = null">{{ $foot['label'] }} <x-icon name="arrow" class="h-3.5 w-3.5" /></a>@endif
    </div>

    @if ($card)
        <div class="min-w-0 self-start">
            <p class="label-caps text-xs font-bold text-rust">{{ $card['eyebrow'] }}</p>
            <a href="{{ $card['href'] }}" class="group mt-4 block overflow-hidden border-t-[3px] border-accent bg-white shadow-card" @click="dd = null">
                <span class="relative block overflow-hidden">
                    @if (! empty($card['media']))<x-picture :media="$card['media']" :alt="$card['media']->alt" sizes="360px" class="aspect-[16/10] w-full object-cover transition-transform duration-700 group-hover:scale-105" />@else<x-picture :src="$card['src']" :alt="$card['alt'] ?? ''" :width="640" :height="400" sizes="360px" class="aspect-[16/10] w-full object-cover transition-transform duration-700 group-hover:scale-105" />@endif
                    @if (! empty($card['tag']))<span class="label-caps absolute left-3 top-3 bg-accent px-3 py-1.5 text-[0.65rem] font-bold text-ink">{{ $card['tag'] }}</span>@endif
                </span>
                <span class="block p-5">
                    <span class="block font-display text-xl font-medium leading-snug text-brand-900">{{ $card['title'] }}</span>
                    @if (! empty($card['meta']))<span class="mt-1.5 block text-sm text-ink/70">{{ $card['meta'] }}</span>@endif
                    <span class="label-caps mt-3 inline-flex items-center gap-2 border-b-2 border-accent pb-0.5 text-[0.7rem] font-bold text-brand-900">{{ $card['cta'] }} <x-icon name="arrow" class="h-3.5 w-3.5" /></span>
                </span>
            </a>
        </div>
    @endif

    <div class="min-w-0 self-start">
        <div class="border-t-[3px] border-accent bg-brand-900 p-7 text-white shadow-card">
            <p class="label-caps text-xs font-bold text-accent">Get in touch</p>
            <p class="mt-4 font-display text-2xl font-medium leading-snug">Talk to a travel specialist</p>
            <p class="mt-3 text-sm leading-relaxed text-white/85">Tell us about the trip you have in mind and we will reply with a free quote.</p>
            <button type="button" class="btn btn-primary mt-5" @click="dd = null; $store.modal.show()">{{ $quote ?: 'Request a quote' }}</button>
            <a href="tel:{{ preg_replace('/[^+\d]/', '', $phone) }}" class="mt-5 flex items-center gap-2 text-sm font-semibold hover:text-accent"><x-icon name="phone" class="h-4 w-4 text-accent" />{{ $phone }}</a>
        </div>
    </div>
</div>
