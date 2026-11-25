{{-- Tour types mega menu (layout from the sister Go Austria site): "Choose how you travel" with a tall photo card per tour type, a "view all" link,
     and a navy "Personal planning" card. Expects $tourTypes (with packages loaded) and $megaQuote. --}}
<div class="container-site grid gap-x-10 gap-y-8 pb-9 pt-8 lg:grid-cols-[minmax(0,3fr)_minmax(0,1fr)]">
    <div class="min-w-0">
        <p class="label-caps text-xs font-bold text-rust">{{ text('mega_tours_heading') ?: 'Choose how you travel' }}</p>
        <div class="mt-5 grid gap-5" style="grid-template-columns: repeat({{ max(1, min($tourTypes->count(), 3)) }}, minmax(0, 1fr))">
            @foreach ($tourTypes->take(3) as $t)
                @php $count = $t->packages->count(); @endphp
                <a href="{{ route('tour-types.show', $t) }}" class="group relative isolate flex aspect-[4/5] flex-col justify-end overflow-hidden rounded-lg bg-brand-900 p-5 text-white" @click="dd = null">
                    <x-picture :src="$t->image ?: 'images/tour-self-drive.webp'" :alt="''" :width="640" :height="800" sizes="320px" class="absolute inset-0 -z-20 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" />
                    <span class="absolute inset-0 -z-10 bg-gradient-to-t from-ink/85 via-ink/25 to-transparent"></span>
                    <span class="font-display text-2xl font-medium leading-tight">{{ $t->name }}</span>
                    <span class="mt-1 block text-sm leading-snug text-white/90">{{ $t->style_note ?: $t->summary }}</span>
                    <span class="label-caps mt-2 text-[0.65rem] font-bold text-accent">{{ $count }} {{ \Illuminate\Support\Str::plural('tour', $count) }}</span>
                </a>
            @endforeach
        </div>
        <a href="{{ route('packages.index') }}" class="label-caps mt-5 inline-flex items-center gap-2 border-b-2 border-accent pb-0.5 text-[0.7rem] font-bold text-brand-900 hover:text-rust" @click="dd = null">View all packages <x-icon name="arrow" class="h-3.5 w-3.5" /></a>
    </div>

    <div class="min-w-0 self-start">
        <div class="border-t-[3px] border-accent bg-brand-900 p-7 text-white shadow-card sm:p-8">
            <p class="label-caps text-xs font-bold text-accent">Personal planning</p>
            <p class="mt-5 font-display text-2xl font-medium leading-snug">Not sure which way to travel?</p>
            <p class="mt-4 text-sm leading-relaxed text-white/85">A specialist will suggest the best fit, free and with no obligation.</p>
            <button type="button" class="btn btn-primary mt-6" @click="dd = null; $store.modal.show()">Plan my trip <x-icon name="arrow" class="h-4 w-4" /></button>
        </div>
    </div>
</div>
