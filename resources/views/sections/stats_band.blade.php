{{-- Numbers band in two columns: the label, heading, text and button on the left, the numbers as a 2 x 2 grid of tiles on the right. Stacks on phones (text first, numbers under it). --}}
<section class="relative isolate overflow-hidden bg-brand-900 py-16 text-white sm:py-24" aria-labelledby="sb-{{ $sid }}">
    @if (! empty($d['image']))
        {{-- Optional photo under a navy overlay (stronger on the text side) so the numbers stay easy to read. --}}
        <x-picture :src="$d['image']" :alt="''" :width="1600" :height="900" class="absolute inset-0 -z-20 h-full w-full object-cover" sizes="100vw" />
        <span class="pointer-events-none absolute inset-0 -z-10 bg-brand-900/68"></span>
        <span class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-r from-brand-900/85 via-brand-900/25 to-transparent"></span>
    @endif
    <div class="container-site grid items-center gap-10 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)] lg:gap-16">
        <div data-reveal="left">
            @if ($d['eyebrow'])
                <p class="label-caps text-white">{{ $d['eyebrow'] }}</p>
                <span class="mb-4 mt-3 flex items-center gap-2" aria-hidden="true"><i class="h-0.5 w-9 bg-white"></i><i class="h-1.5 w-1.5 rotate-45 bg-white"></i><i class="h-0.5 w-9 bg-white"></i></span>
            @endif
            <h2 id="sb-{{ $sid }}" class="text-3xl font-medium sm:text-4xl lg:text-5xl">{!! emphasize($d['heading'], 'text-accent') !!}</h2>
            @if ($d['text'])<p class="mt-5 max-w-xl text-base leading-relaxed text-white/90 sm:text-lg">{{ $d['text'] }}</p>@endif
            @if (! empty($d['points']))
                <ul class="mt-6 max-w-xl space-y-3">
                    @foreach ($d['points'] as $pt)
                        <li class="flex gap-3 text-base leading-snug text-white/95"><span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-accent text-ink"><x-icon name="check" class="h-3 w-3" /></span>{{ $pt['text'] }}</li>
                    @endforeach
                </ul>
            @endif
            @if ($d['button_label'] || ! empty($d['link_label']))
                <div class="mt-8 flex flex-wrap items-center gap-x-6 gap-y-3">
                    @if ($d['button_label'])<x-button @click="$store.modal.show()">{{ $d['button_label'] }}</x-button>@endif
                    @if (! empty($d['link_label']) && ! empty($d['link_url']))<a href="{{ str_starts_with($d['link_url'], '/') ? url($d['link_url']) : $d['link_url'] }}" class="inline-flex items-center gap-2 border-b-2 border-accent pb-0.5 text-sm font-semibold text-white hover:text-accent">{{ $d['link_label'] }} <x-icon name="arrow" class="h-4 w-4" /></a>@endif
                </div>
            @endif
        </div>
        @if (count($d['items']))
            <dl class="grid grid-cols-2 gap-4 sm:gap-5" data-reveal="right">
                @foreach ($d['items'] as $i => $it)
                    <div class="flex flex-col justify-center rounded-xl border border-white/15 bg-brand-900/70 backdrop-blur-sm px-5 py-7 text-center sm:py-9">
                        <dd class="order-1 font-display text-4xl font-medium leading-none text-accent sm:text-5xl">{{ $it['value'] }}</dd>
                        <dt class="order-2 mt-3 text-xs font-semibold uppercase tracking-widest text-white/85 sm:text-sm">{{ $it['label'] }}</dt>
                    </div>
                @endforeach
            </dl>
        @endif
    </div>
</section>
