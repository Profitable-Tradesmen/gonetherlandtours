@if (count($d['items']))
{{-- A clean white row of selling points under the hero: icon tile + title + one line, thin dividers between them. --}}
<section class="border-b border-sand-200 bg-white" aria-label="Why choose us">
    <div class="container-site grid gap-y-6 py-8 sm:grid-cols-2 lg:grid-cols-4 lg:gap-y-0">
        @foreach ($d['items'] as $i => $it)
            <div class="flex items-start gap-4 lg:px-6 {{ $i > 0 ? 'lg:border-l lg:border-sand-200' : 'lg:pl-0' }}" data-reveal="up" style="--d:{{ $i * 80 }}ms">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center bg-brand-900 text-accent"><x-icon :name="$it['icon'] ?: 'check'" class="h-7 w-7" /></span>
                <div>
                    <h3 class="font-display text-lg font-medium leading-tight text-brand-900">{{ $it['title'] }}</h3>
                    <p class="mt-1 text-sm leading-snug text-ink/75">{{ $it['text'] }}</p>
                </div>
            </div>
        @endforeach
    </div>
</section>
@endif
