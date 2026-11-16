{{-- HTML fragment returned by packages.search (location search popup). --}}
@if ($term === '')
    <p class="py-6 text-center text-sm text-ink/60">Type a place, such as a city or region, to see matching packages.</p>
@elseif ($packages->isEmpty())
    <p class="py-6 text-center text-sm">No packages found for &ldquo;{{ $term }}&rdquo; yet. <a href="{{ route('contact') }}" class="font-semibold text-rust underline">Ask us to build one for you</a>.</p>
@else
    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-ink/60">{{ $packages->count() }} {{ Str::plural('package', $packages->count()) }} found</p>
    <ul class="divide-y divide-sand-200">
        @foreach ($packages as $p)
            @php
                $cover = $p->images->first();
                $thumb = $cover ? $cover->url() : asset($p->tourType?->image ?: 'images/tour-self-drive.webp');
            @endphp
            <li>
                <a href="{{ route('packages.show', $p) }}" class="flex items-center gap-3 py-3 hover:bg-sand/60">
                    <img src="{{ $thumb }}" alt="" width="80" height="60" loading="lazy" class="h-14 w-20 shrink-0 rounded-lg object-cover">
                    <span class="min-w-0 flex-1">
                        <span class="block truncate font-semibold">{{ $p->title }}</span>
                        <span class="block text-xs text-ink/70">{{ $p->destination?->name }}@if ($p->destination) &middot; @endif{{ $p->nights }} nights</span>
                    </span>
                    <span class="shrink-0 text-sm font-semibold text-brand-700">{{ $p->priceLabel() }}</span>
                </a>
            </li>
        @endforeach
    </ul>
@endif
