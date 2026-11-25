@php
    // Placeholder contact details are shown until Admin > Settings has the real ones (same as the top bar).
    $phone = setting('phone') ?: '+1 000-000-0000'; $email = setting('email') ?: 'info@gonetherlandtours.com'; $address = setting('address');   // blank hides the line
    $footerLinks = \App\Models\MenuItem::tree('footer');
@endphp
<footer class="bg-ink text-white/85">
    {{-- Desktop: a brand column on the left and a wider area on the right with three link columns (tours, destinations, company) Phones and tablets stack them. --}}
    <div class="container-site grid gap-x-10 gap-y-10 py-12 lg:grid-cols-[minmax(0,1fr)_minmax(0,2fr)]">
        <div>
            <a href="{{ route('home') }}" class="inline-block">@include('partials.logo', ['light' => true])</a>
            <p class="mt-4 text-sm leading-relaxed">{{ setting('footer_text') }}</p>
            <ul class="mt-5 space-y-2.5 text-sm">
@if ($phone)                <li class="flex items-center gap-3"><span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white/10 text-accent"><x-icon name="phone" class="h-4 w-4" /></span><a href="tel:{{ preg_replace('/[^+\d]/', '', $phone) }}" class="inline-block py-1 hover:text-accent">{{ $phone }}</a></li>@endif
                <li class="flex items-center gap-3"><span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white/10 text-accent"><x-icon name="mail" class="h-4 w-4" /></span><a href="mailto:{{ $email }}" class="inline-block py-1 hover:text-accent">{{ $email }}</a></li>
                @if ($address)<li class="flex items-start gap-3"><span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white/10 text-accent"><x-icon name="map" class="h-4 w-4" /></span><span class="py-1.5">{{ $address }}</span></li>@endif
            </ul>
            <ul class="mt-4 flex gap-4 text-sm">
                @foreach (['facebook' => 'Facebook', 'instagram' => 'Instagram', 'youtube' => 'YouTube'] as $k => $label)
                    @if (setting('social_'.$k))<li><a href="{{ setting('social_'.$k) }}" rel="noopener" target="_blank" class="hover:text-accent">{{ $label }}</a></li>@endif
                @endforeach
            </ul>
        </div>
        <div class="grid gap-x-10 gap-y-8 sm:grid-cols-3">
        <nav aria-label="{{ text('footer_tours_heading') ?: 'Tours' }}">
            <h2 class="label-caps pb-1 text-accent !font-sans">{{ text('footer_tours_heading') }}</h2>
            <ul class="mt-3 space-y-0.5 text-sm">
                @foreach ($tourTypes as $t)<li><a href="{{ route('tour-types.show', $t) }}" class="inline-block py-1.5 hover:text-accent">{{ $t->name }}</a></li>@endforeach
            </ul>
        </nav>
        @if ($destinations->isNotEmpty())
        <nav aria-label="{{ text('footer_destinations_heading') ?: 'Destinations' }}">
            <h2 class="label-caps pb-1 text-accent !font-sans">{{ text('footer_destinations_heading') ?: 'Destinations' }}</h2>
            <ul class="mt-3 space-y-0.5 text-sm">
                @foreach ($destinations as $d)<li><a href="{{ route('destinations.show', $d) }}" class="inline-block py-1.5 hover:text-accent">{{ $d->name }}</a></li>@endforeach
            </ul>
        </nav>
        @endif
        <nav aria-label="{{ text('footer_company_heading') ?: 'Company' }}">
            <h2 class="label-caps pb-1 text-accent !font-sans">{{ text('footer_company_heading') }}</h2>
            <ul class="mt-3 space-y-0.5 text-sm">
                @foreach ($footerLinks as $link)
                    <li><a href="{{ $link->href() }}" class="inline-block py-1.5 hover:text-accent" @if ($link->new_tab) target="_blank" rel="noopener" @endif>{{ $link->label }}</a></li>
                @endforeach
            </ul>
        </nav>
        </div>
    </div>
    <div class="py-5 text-center text-xs text-white/60">
        &copy; {{ date('Y') }} {{ company_name() }}. {{ text('footer_copyright') }}
    </div>
</footer>
