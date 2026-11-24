@if (count($d['items']))
@php
    // one column per point (up to four), so three points are spread evenly across the full width instead of leaving an empty column
    $n = min(count($d['items']), 4);
    $cols = [1 => 'grid-cols-1', 2 => 'grid-cols-2', 3 => 'grid-cols-2 sm:grid-cols-3', 4 => 'grid-cols-2 lg:grid-cols-4'][$n];
@endphp
{{-- A clean white row of selling points under the hero. Each cell is the same height with the icon tile and the text centered on one line, thin dividers between cells, so a longer text never pushes its neighbors out of line. --}}
<section class="tight-pad border-b border-sand-200 bg-white py-5" aria-label="Why choose us">
    <div class="container-site grid items-stretch gap-x-3 gap-y-4 lg:gap-y-0 {{ $cols }}">
        @foreach ($d['items'] as $i => $it)
            <div class="flex items-center gap-2.5 px-1 sm:justify-center sm:gap-3 sm:px-2 lg:px-5 lg:py-2 {{ $i > 0 ? 'lg:border-l lg:border-sand-200' : '' }}" data-reveal="up" style="--d:{{ $i * 80 }}ms">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center bg-brand-900 text-accent sm:h-12 sm:w-12"><x-icon :name="$it['icon'] ?: 'check'" class="h-6 w-6" /></span>
                <div class="min-w-0">
                    <p class="font-display text-base font-medium leading-tight text-brand-900 sm:text-lg">{{ $it['title'] }}</p>
                    <p class="mt-1 hidden text-sm leading-snug text-ink/75 sm:block">{{ $it["text"] }}</p>
                </div>
            </div>
        @endforeach
    </div>
</section>
@endif
