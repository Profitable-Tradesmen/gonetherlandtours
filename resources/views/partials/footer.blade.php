@php
    $phone = setting('phone'); $email = setting('email'); $address = setting('address');
    $footerLinks = \App\Models\MenuItem::tree('footer');
@endphp
<footer class="bg-ink text-white/85">
    <div class="container-site grid gap-10 py-14 md:grid-cols-4">
        <div class="md:col-span-2">
            <a href="{{ route('home') }}">@include('partials.logo', ['light' => true])</a>
            <p class="mt-4 max-w-md text-sm leading-relaxed">{{ setting('footer_text') }}</p>
            <ul class="mt-5 space-y-2 text-sm">
                @if ($phone)<li><a href="tel:{{ preg_replace('/[^+\d]/', '', $phone) }}" class="hover:text-accent">{{ $phone }}</a></li>@endif
                @if ($email)<li><a href="mailto:{{ $email }}" class="hover:text-accent">{{ $email }}</a></li>@endif
                @if ($address)<li>{{ $address }}</li>@endif
            </ul>
            <ul class="mt-4 flex gap-4 text-sm">
                @foreach (['facebook' => 'Facebook', 'instagram' => 'Instagram', 'youtube' => 'YouTube'] as $k => $label)
                    @if (setting('social_'.$k))<li><a href="{{ setting('social_'.$k) }}" rel="noopener" target="_blank" class="hover:text-accent">{{ $label }}</a></li>@endif
                @endforeach
            </ul>
        </div>
        <nav aria-label="{{ text('footer_tours_heading') ?: 'Tours' }}">
            <h2 class="label-caps text-accent !font-sans">{{ text('footer_tours_heading') }}</h2>
            <ul class="mt-4 space-y-2 text-sm">
                @foreach ($tourTypes as $t)<li><a href="{{ route('tour-types.show', $t) }}" class="hover:text-accent">{{ $t->name }}</a></li>@endforeach
                @foreach ($destinations as $d)<li><a href="{{ route('destinations.show', $d) }}" class="hover:text-accent">{{ $d->name }}</a></li>@endforeach
            </ul>
        </nav>
        <nav aria-label="{{ text('footer_company_heading') ?: 'Company' }}">
            <h2 class="label-caps text-accent !font-sans">{{ text('footer_company_heading') }}</h2>
            <ul class="mt-4 space-y-2 text-sm">
                @foreach ($footerLinks as $link)
                    <li><a href="{{ $link->href() }}" class="hover:text-accent" @if ($link->new_tab) target="_blank" rel="noopener" @endif>{{ $link->label }}</a></li>
                @endforeach
            </ul>
        </nav>
    </div>
    <div class="border-t border-white/10 py-5 text-center text-xs text-white/60">
        &copy; {{ date('Y') }} {{ company_name() }}. {{ text('footer_copyright') }}
    </div>
</footer>
