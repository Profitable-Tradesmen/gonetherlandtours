{{-- Travel styles mega menu: a photo card per style (three across), a "view all" link and the navy planning card, matching the tour types menu.
     Expects $styles and $megaQuote. --}}
<div class="container-site grid gap-x-10 gap-y-8 pb-9 pt-8 lg:grid-cols-[minmax(0,3fr)_minmax(0,1fr)]">
    <div class="min-w-0">
        <p class="label-caps text-xs font-bold text-rust">Travel by interest</p>
        <ul class="mt-5 grid grid-cols-2 gap-4 xl:grid-cols-3">
            @foreach ($styles->take(6) as $s)
                <li>
                    <a href="{{ route('travel-styles.show', $s) }}" class="group relative isolate flex h-36 flex-col justify-end overflow-hidden rounded-lg bg-brand-900 p-4 text-white xl:h-40" @click="dd = null">
                        @if ($s->image)<x-picture :src="$s->image" :alt="''" :width="640" :height="360" sizes="340px" class="absolute inset-0 -z-20 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" />@endif
                        <span class="absolute inset-0 -z-10 bg-gradient-to-t from-ink/90 via-ink/40 to-transparent"></span>
                        <span class="font-display text-xl font-medium leading-tight">{{ $s->name }}</span>
                        <span class="mt-0.5 line-clamp-1 text-sm text-white/90">{{ $s->summary }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
        <a href="{{ route('travel-styles.index') }}" class="label-caps mt-5 inline-flex items-center gap-2 border-b-2 border-accent pb-0.5 text-[0.7rem] font-bold text-brand-900 hover:text-rust" @click="dd = null">All travel styles <x-icon name="arrow" class="h-3.5 w-3.5" /></a>
    </div>

    <div class="min-w-0 self-start">
        <div class="border-t-[3px] border-accent bg-brand-900 p-7 text-white shadow-card sm:p-8">
            <p class="label-caps text-xs font-bold text-accent">Mix and match</p>
            <p class="mt-5 font-display text-2xl font-medium leading-snug">Love more than one?</p>
            <p class="mt-4 text-sm leading-relaxed text-white/85">Tell us your favorites and we will blend them into one trip, with a free, no-obligation quote.</p>
            <button type="button" class="btn btn-primary mt-6" @click="dd = null; $store.modal.show()">{{ $megaQuote }} <x-icon name="arrow" class="h-4 w-4" /></button>
        </div>
    </div>
</div>
