@if (count($d['items']))
@php $n = count($d['items']); $cols = $n === 3 ? 'sm:grid-cols-3 lg:grid-cols-3 lg:gap-8' : 'sm:grid-cols-2 lg:grid-cols-4'; @endphp
@if (($d['style'] ?? 'cards') === 'strip')
{{-- Slim navy strip: icon beside a title and one line, thin dividers between the items; a swipe row on phones. --}}
<section class="tight-pad bg-brand-900 text-white" aria-label="What sets us apart">
    <div class="container-site">
        <x-slider :light="true" class="grid gap-6 sm:gap-0 sm:grid-cols-{{ min($n, 4) }}">
            @foreach ($d['items'] as $i => $c)
                <div class="flex items-center gap-4 sm:px-6 {{ $i > 0 ? 'sm:border-l sm:border-white/25' : 'sm:pl-0' }}">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center text-accent"><x-icon :name="$c['icon'] ?: 'check'" class="h-9 w-9" /></span>
                    <div class="min-w-0">
                        <h2 class="font-display text-xl font-medium leading-tight">{{ $c['title'] }}</h2>
                        <p class="mt-1 text-sm leading-snug text-white/80">{{ $c['text'] }}</p>
                    </div>
                </div>
            @endforeach
        </x-slider>
    </div>
</section>
@else
<section class="bg-brand-900 py-12 text-white sm:py-16" aria-label="What sets us apart">
    <div class="container-site">
        <x-slider class="grid gap-5 {{ $cols }}">
            @foreach ($d['items'] as $c)
                <div class="rounded-2xl bg-white p-6 text-center text-ink shadow-card">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-sand text-brand-700"><x-icon :name="$c['icon'] ?: 'check'" class="h-7 w-7" /></span>
                    <h2 class="mt-3 font-sans text-base font-bold">{{ $c['title'] }}</h2>
                    <p class="mt-2 text-sm leading-relaxed text-ink/75">{{ $c['text'] }}</p>
                </div>
            @endforeach
        </x-slider>
    </div>
</section>
@endif
@endif
