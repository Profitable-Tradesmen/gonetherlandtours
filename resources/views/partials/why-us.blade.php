{{-- "Why travel with us": the four selling points as a compact navy strip (navy so it never blends into the white or sand sections around it). Wording comes from Admin > Settings (usp_*), so it is the same
     on every page. Optional $heading overrides the strip heading. Used on package, tour type and destination pages and by the "Why choose us strip" section. --}}
@php
    $icons = ['tulip', 'guide', 'search', 'map'];
    $points = collect(range(1, 4))->map(fn ($n) => ['icon' => $icons[$n - 1], 'title' => text("usp_{$n}_title"), 'text' => text("usp_{$n}_text")])->filter(fn ($p) => $p['title'] !== '');
    $stripHeading = ($heading ?? '') !== '' ? $heading : text('usp_heading');
@endphp
@if ($points->isNotEmpty())
<section class="bg-brand-900 py-10 text-white sm:py-12" aria-label="{{ $stripHeading ?: 'Why travel with us' }}">
    <div class="container-site">
        @if ($stripHeading)<p class="label-caps mb-5 text-center text-accent">{{ $stripHeading }}</p>@endif
        <ul class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($points as $p)
                <li class="flex items-start gap-3">
                    <span class="mt-0.5 flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-white/10 text-accent"><x-icon :name="$p['icon']" class="h-5 w-5" /></span>
                    <div>
                        <h3 class="font-sans text-base font-bold leading-snug">{{ $p['title'] }}</h3>
                        @if ($p['text'])<p class="mt-0.5 text-sm leading-relaxed text-white/75">{{ $p['text'] }}</p>@endif
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</section>
@endif
