{{-- "Places and map" of a tour: a simple map of the Netherlands with the stops numbered in route order, and the same list beside it. Expects $places. --}}
@php
    use App\Support\NetherlandsMap as NM;
    $pins = [];
    foreach ($places as $i => $p) {
        if ($p->lat === null || $p->lon === null) { continue; }
        [$x, $y] = NM::xy($p->lon, $p->lat);
        $pins[$i + 1] = ['x' => $x, 'y' => $y, 'px' => $x, 'py' => $y, 'name' => $p->name];
    }
    $route = collect($pins)->map(fn ($p) => $p['x'].' '.$p['y'])->implode(' L');
    // Towns that sit very close together get their number circles nudged apart (a thin line points back to the real spot).
    for ($round = 0; $round < 40; $round++) {
        foreach ($pins as $a => $pa) {
            foreach ($pins as $b => $pb) {
                if ($b <= $a) { continue; }
                $dx = $pins[$b]['px'] - $pins[$a]['px']; $dy = $pins[$b]['py'] - $pins[$a]['py'];
                $dist = max(0.1, hypot($dx, $dy));
                if ($dist < 27) {
                    $push = (27 - $dist) / 2; $ux = $dist > 0.2 ? $dx / $dist : 1; $uy = $dist > 0.2 ? $dy / $dist : 0;
                    $pins[$a]['px'] -= $ux * $push; $pins[$a]['py'] -= $uy * $push;
                    $pins[$b]['px'] += $ux * $push; $pins[$b]['py'] += $uy * $push;
                }
            }
        }
    }
@endphp
<div class="grid items-start gap-8 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.15fr)] lg:gap-12">
    <figure class="mx-auto w-full max-w-xl lg:sticky lg:top-40">
        <div class="overflow-hidden rounded-3xl bg-brand-900 shadow-card">
            <svg viewBox="0 0 {{ NM::WIDTH }} {{ NM::HEIGHT }}" class="block h-auto w-full" role="img" aria-label="Map of the Netherlands showing the stops in order: {{ $places->pluck('name')->implode(', ') }}">
                <path d="{{ NM::landPath() }}" fill="rgb(255 255 255 / .09)" fill-rule="evenodd" stroke="#F2B743" stroke-opacity=".75" stroke-width="1.6" stroke-dasharray="1.5 4.5" stroke-linecap="round" stroke-linejoin="round" />
                @if (count($pins) > 1)<path d="M{{ $route }}" fill="none" stroke="#fff" stroke-opacity=".7" stroke-width="1.6" stroke-dasharray="6 6" stroke-linecap="round" stroke-linejoin="round" />@endif
                @foreach ($pins as $n => $pin)
                    @if (hypot($pin['px'] - $pin['x'], $pin['py'] - $pin['y']) > 4)
                        <line x1="{{ $pin['x'] }}" y1="{{ $pin['y'] }}" x2="{{ round($pin['px'], 1) }}" y2="{{ round($pin['py'], 1) }}" stroke="#F2B743" stroke-width="1.2" />
                        <circle cx="{{ $pin['x'] }}" cy="{{ $pin['y'] }}" r="2.5" fill="#F2B743" />
                    @endif
                @endforeach
                @foreach ($pins as $n => $pin)
                    <circle cx="{{ round($pin['px'], 1) }}" cy="{{ round($pin['py'], 1) }}" r="12.5" fill="#F2B743" stroke="#1F4660" stroke-width="2" />
                    <text x="{{ round($pin['px'], 1) }}" y="{{ round($pin['py'] + 4.5, 1) }}" text-anchor="middle" font-size="13" font-weight="800" fill="#1F4660" style="font-family:'Manrope Variable',system-ui,sans-serif">{{ $n }}</text>
                @endforeach
            </svg>
        </div>
        <figcaption class="mt-2 text-center text-xs text-ink/60">Simplified map; positions are approximate.</figcaption>
    </figure>
    <ol class="space-y-4">
        @foreach ($places as $i => $p)
            <li class="flex flex-col gap-4 rounded-2xl border border-ink/10 bg-white p-3 shadow-sm sm:flex-row sm:items-center">
                @if ($p->image)<x-picture :src="$p->image" :alt="$p->image_alt ?? ''" :width="1600" :height="1067" class="aspect-[3/2] h-auto w-full shrink-0 rounded-xl object-cover sm:w-44" sizes="(min-width:640px) 11rem, 90vw" />@endif
                <div class="min-w-0 px-1 pb-2 sm:pb-0">
                    <h3 class="flex items-center gap-3 text-xl font-medium"><span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-accent font-sans text-sm font-extrabold text-brand-900" aria-hidden="true">{{ $i + 1 }}</span>{{ $p->name }}</h3>
                    @if ($p->note)<p class="mt-2 text-sm leading-relaxed text-ink/80">{{ $p->note }}</p>@endif
                    @if ($p->destination)<a href="{{ route('destinations.show', $p->destination) }}" class="mt-1 inline-flex min-h-[2.75rem] items-center gap-1.5 text-sm font-bold text-brand-700 hover:text-rust"><span class="border-b border-current pb-0.5">More in {{ $p->destination->name }}</span><x-icon name="arrow" class="h-4 w-4" /></a>@endif
                </div>
            </li>
        @endforeach
    </ol>
</div>
