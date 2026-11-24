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
                <p class="label-caps text-accent">{{ $d['eyebrow'] }}</p>
                <span class="mb-4 mt-3 flex items-center gap-2" aria-hidden="true"><i class="h-0.5 w-9 bg-accent"></i><i class="h-1.5 w-1.5 rotate-45 bg-accent"></i><i class="h-0.5 w-9 bg-accent"></i></span>
            @endif
            <h2 id="sb-{{ $sid }}" class="text-3xl font-medium sm:text-4xl lg:text-5xl">{!! emphasize($d['heading'], 'text-accent') !!}</h2>
            @if ($d['text'])<p class="mt-5 max-w-xl text-base leading-relaxed text-white/90 sm:text-lg">{{ $d['text'] }}</p>@endif
            @if ($d['button_label'])<p class="mt-8"><x-button @click="$store.modal.show()">{{ $d['button_label'] }}</x-button></p>@endif
        </div>
        @if (count($d['items']))
            <dl class="grid grid-cols-2 gap-4 sm:gap-5" data-reveal="right">
                @foreach ($d['items'] as $i => $it)
                    <div class="flex flex-col justify-center rounded-xl border border-white/15 border-t-[3px] border-t-accent bg-brand-900/70 backdrop-blur-sm px-5 py-7 text-center sm:py-9">
                        <dd class="order-1 font-display text-4xl font-medium leading-none text-accent sm:text-5xl">{{ $it['value'] }}</dd>
                        <dt class="order-2 mt-3 text-xs font-semibold uppercase tracking-widest text-white/85 sm:text-sm">{{ $it['label'] }}</dt>
                    </div>
                @endforeach
            </dl>
        @endif
    </div>
</section>
