@if (count($d['items']))
{{-- A clean white row of selling points under the hero. Each cell is the same height with the icon tile and the text centered on one line, thin dividers between cells, so a longer text never pushes its neighbours out of line. --}}
<section class="border-b border-sand-200 bg-white" aria-label="Why choose us">
    <div class="container-site grid items-stretch gap-y-5 py-6 sm:grid-cols-2 lg:grid-cols-4 lg:gap-y-0">
        @foreach ($d['items'] as $i => $it)
            <div class="flex items-center gap-3 lg:px-5 lg:py-2 {{ $i > 0 ? 'lg:border-l lg:border-sand-200' : 'lg:pl-0' }}" data-reveal="up" style="--d:{{ $i * 80 }}ms">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center bg-brand-900 text-accent"><x-icon :name="$it['icon'] ?: 'check'" class="h-6 w-6" /></span>
                <div class="min-w-0">
                    <h3 class="font-display text-lg font-medium leading-tight text-brand-900">{{ $it['title'] }}</h3>
                    <p class="mt-1 text-sm leading-snug text-ink/75">{{ $it['text'] }}</p>
                </div>
            </div>
        @endforeach
    </div>
</section>
@endif
