@if (count($d['items']))
@php
    // one column per point (up to four), so three points are spread evenly across the full width instead of leaving an empty column
    $n = min(count($d['items']), 4);
    $cols = [1 => 'sm:grid-cols-1 lg:grid-cols-1', 2 => 'sm:grid-cols-2 lg:grid-cols-2', 3 => 'sm:grid-cols-3 lg:grid-cols-3', 4 => 'sm:grid-cols-2 lg:grid-cols-4'][$n];
@endphp
{{-- A clean white row of selling points under the hero. Each cell is the same height with the icon tile and the text centered on one line, thin dividers between cells, so a longer text never pushes its neighbors out of line. --}}
<section class="hidden border-b border-sand-200 bg-white sm:block" aria-label="Why choose us">
    <div class="container-site grid items-stretch gap-y-5 py-6 lg:gap-y-0 {{ $cols }}">
        @foreach ($d['items'] as $i => $it)
            <div class="flex items-center justify-center gap-3 px-2 lg:px-5 lg:py-2 {{ $i > 0 ? 'lg:border-l lg:border-sand-200' : '' }}" data-reveal="up" style="--d:{{ $i * 80 }}ms">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center bg-brand-900 text-accent"><x-icon :name="$it['icon'] ?: 'check'" class="h-6 w-6" /></span>
                <div class="min-w-0">
                    <p class="font-display text-lg font-medium leading-tight text-brand-900">{{ $it['title'] }}</p>
                    <p class="mt-1 text-sm leading-snug text-ink/75">{{ $it['text'] }}</p>
                </div>
            </div>
        @endforeach
    </div>
</section>
@endif
