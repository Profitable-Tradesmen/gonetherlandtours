@php
    $phone = setting('phone') ?: '+1 000-000-0000';
    $email = setting('email') ?: 'info@gonetherlandtours.com';
    $card = 'group flex items-center gap-4 rounded-2xl bg-sand p-4 transition';
    $icon = 'flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-brand-900 text-accent';
    $address = setting('address');
@endphp
<section class="container-site grid gap-10 py-12 lg:grid-cols-[1fr_1.2fr] lg:gap-24 lg:py-20 xl:gap-28">
    <div>
        <div class="prose-site">{!! clean_html($d['intro']) !!}</div>

        @if ($d['specialist_name'])
            <figure class="mt-6 flex items-start gap-4 rounded-2xl border border-sand-200 bg-white p-4 shadow-card">
                @if ($d['specialist_image'])<x-picture :src="$d['specialist_image']" :alt="'Placeholder portrait of '.$d['specialist_name']" :width="224" :height="224" class="h-16 w-16 shrink-0 rounded-full object-cover" />@endif
                <figcaption class="min-w-0">
                    <p class="font-display text-lg leading-tight">{{ $d['specialist_name'] }}</p>
                    @if ($d['specialist_role'])<p class="label-caps mt-0.5 text-[0.65rem] text-rust">{{ $d['specialist_role'] }}</p>@endif
                    @if ($d['specialist_text'])<p class="mt-2 text-sm leading-relaxed text-ink/75">{{ $d['specialist_text'] }}</p>@endif
                </figcaption>
            </figure>
        @endif

        @if ($d['reach_heading'])<h2 class="mt-8 font-display text-2xl font-medium sm:text-3xl">{{ $d['reach_heading'] }}</h2>@endif
        <ul class="mt-4 space-y-3">
@if ($phone)            <li><a href="tel:{{ preg_replace('/[^+\d]/', '', $phone) }}" class="{{ $card }} hover:bg-sand-200">
                <span class="{{ $icon }}"><x-icon name="phone" class="h-5 w-5" /></span>
                <span class="min-w-0"><span class="block font-display text-lg leading-tight">Call us</span><span class="block text-sm text-ink/70">{{ $d['call_text'] }}</span><span class="block font-semibold text-brand-700 group-hover:text-rust">{{ $phone }}</span></span>
            </a></li>
            <li><a href="mailto:{{ $email }}" class="{{ $card }} hover:bg-sand-200">@endif
                <span class="{{ $icon }}"><x-icon name="mail" class="h-5 w-5" /></span>
                <span class="min-w-0"><span class="block font-display text-lg leading-tight">Email us</span><span class="block text-sm text-ink/70">{{ $d['email_text'] }}</span><span class="block break-all font-semibold text-brand-700 group-hover:text-rust">{{ $email }}</span></span>
            </a></li>
            @if ($d['hours_text'])
                <li class="{{ $card }}">
                    <span class="{{ $icon }}"><x-icon name="clock" class="h-5 w-5" /></span>
                    <span class="min-w-0"><span class="block font-display text-lg leading-tight">{{ $d['hours_title'] }}</span><span class="block text-sm leading-relaxed text-ink/70">{{ $d['hours_text'] }}</span></span>
                </li>
            @endif
            @if ($address)
                {{-- Only shown when a real address is set in Admin > Settings; a link to the map, not an embedded map, so the page stays light. --}}
                <li><a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($address) }}" target="_blank" rel="noopener" class="{{ $card }} hover:bg-sand-200">
                    <span class="{{ $icon }}"><x-icon name="map" class="h-5 w-5" /></span>
                    <span class="min-w-0"><span class="block font-display text-lg leading-tight">Visit us</span><span class="block text-sm text-ink/70">{{ $address }}</span><span class="block font-semibold text-brand-700 group-hover:text-rust">Open in Maps &rarr;</span></span>
                </a></li>
            @endif
        </ul>

        @if ($d['explore_heading'])
            <h2 class="mt-8 font-display text-xl font-medium">{{ $d['explore_heading'] }}</h2>
            <ul class="mt-3 flex flex-wrap gap-x-6 gap-y-2 text-sm font-semibold text-brand-700">
                <li><a href="{{ route('packages.index') }}" class="inline-flex items-center gap-1.5 hover:text-rust">Browse our packages <x-icon name="arrow" class="h-4 w-4" /></a></li>
                <li><a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 hover:text-rust">Take the 30-second quiz <x-icon name="arrow" class="h-4 w-4" /></a></li>
                <li><button type="button" class="inline-flex items-center gap-1.5 hover:text-rust" @click="$store.modal.show()">Ask about a custom trip <x-icon name="arrow" class="h-4 w-4" /></button></li>
            </ul>
        @endif
    </div>

    <div x-data="contactPrompts('contact-special_request')">
        @if (count($d['prompts']))
            <div class="mb-4">
                @if ($d['prompts_heading'])<p class="text-sm font-semibold text-ink">{{ $d['prompts_heading'] }}</p>@endif
                <div class="mt-2 flex flex-wrap gap-2">
                    @foreach ($d['prompts'] as $p)
                        <button type="button" class="rounded-full border border-brand-700/40 bg-white px-3.5 py-1.5 text-sm font-medium text-brand-900 transition hover:border-brand-700 hover:bg-sand" data-text="{{ $p['text'] }}" @click="fill($el.dataset.text)">{{ $p['label'] }}</button>
                    @endforeach
                </div>
            </div>
        @endif
        <div class="rounded-2xl border-4 border-accent bg-white p-5 shadow-card sm:p-6">
            @if ($d['reassure'])<p class="mb-4 flex items-center gap-2 text-sm font-medium text-brand-900"><x-icon name="lock" class="h-4 w-4 shrink-0 text-rust" />{{ $d['reassure'] }}</p>@endif
            <x-enquiry-form form="contact" idp="contact" extra :submit="$d['submit_label']" />
        </div>
    </div>
</section>
