@if (count($d['items']))
<section class="bg-sand py-16 sm:py-24" aria-labelledby="up-{{ $sid }}">
    <div class="container-site">
        <x-section-heading data-reveal="up" :eyebrow="$d['eyebrow'] ?: null" :title="$d['heading']" :id="'up-'.$sid" />
        {{-- Each selling point with something checkable behind it: a real example (the "proof" box) and a link to read more.
             From md up the grid uses subgrid: every card spans 3 rows (heading, proof, link), so the proof boxes and links line up
             across a row of cards even when one claim wraps to more lines. Every card always renders all 3 parts (empty if unused). --}}
        <x-slider class="grid gap-6 md:grid-cols-2">
            @foreach ($d['items'] as $i => $it)
                @php $link = trim((string) $it['link']); $href = $link !== '' && str_starts_with($link, '/') ? url($link) : $link; @endphp
                <article class="flex flex-col rounded-2xl bg-white p-6 shadow-card ring-1 ring-sand-200 md:row-span-3 md:grid md:grid-rows-subgrid md:gap-y-4" data-reveal="up" style="--d:{{ ($i % 2) * 120 }}ms">
                    <div class="flex items-start gap-4">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-sand text-rust"><x-icon :name="$it['icon'] ?: 'check'" class="h-6 w-6" /></span>
                        <div>
                            <h3 class="font-display text-xl font-medium">{{ $it['title'] }}</h3>
                            <p class="mt-1 text-sm leading-relaxed text-ink/75">{{ $it['text'] }}</p>
                        </div>
                    </div>
                    @if ($it['proof'])
                        <div class="mt-4 flex-1 rounded-xl border-l-4 border-accent bg-sand px-4 py-3 md:mt-0">
                            <p class="label-caps text-[0.65rem] text-rust">{{ $d['proof_label'] ?: 'In practice' }}</p>
                            <p class="mt-1 text-sm leading-relaxed">{{ $it['proof'] }}</p>
                        </div>
                    @else
                        <div class="hidden md:block"></div>
                    @endif
                    @if ($it['link_label'] && $href !== '')
                        <a href="{{ $href }}" class="mt-2 inline-flex min-h-[2.75rem] items-center gap-2 self-start text-sm font-bold text-brand-700 underline underline-offset-4 hover:text-rust md:mt-0">{{ $it['link_label'] }} <x-icon name="arrow" class="h-4 w-4" /></a>
                    @else
                        <div class="hidden md:block"></div>
                    @endif
                </article>
            @endforeach
        </x-slider>
    </div>
</section>
@endif
