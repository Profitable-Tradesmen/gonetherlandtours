{{-- A row of photo cards for a mega menu: photo, title with a short accent underline, one line of text and a "Discover" link. Expects $cards (href, image, alt, title, text, new_tab) and $link. --}}
@php $n = max($cards->count(), 1); @endphp
<div class="container-site grid gap-6 pb-7 pt-8" style="grid-template-columns: repeat({{ min($n, 6) }}, minmax(0, 1fr))">
    @foreach ($cards as $c)
        <a href="{{ $c['href'] }}" class="group flex min-w-0 flex-col" @click="dd = null" @if ($c['new_tab']) target="_blank" rel="noopener" @endif>
            <span class="block overflow-hidden rounded-lg bg-white/10">
                @if ($c['image'])<x-picture :src="$c['image']" :alt="$c['alt']" :width="640" :height="400" sizes="(min-width:1280px) 18vw, 25vw" class="aspect-[3/2] w-full object-cover transition-transform duration-700 group-hover:scale-105" />@else<span class="block aspect-[3/2]"></span>@endif
            </span>
            <span class="mt-4 block font-display text-xl font-medium leading-tight transition-colors group-hover:text-accent">{{ $c['title'] }}</span>
            <span class="mt-2 block h-0.5 w-9 bg-accent/80" aria-hidden="true"></span>
            @if ($c['text'])<span class="mt-3 block text-sm leading-snug text-white/75">{{ $c['text'] }}</span>@endif
            @if ($link)<span class="label-caps mt-auto inline-flex items-center gap-2 pt-3 text-[0.7rem] text-accent">{{ $link }} <x-icon name="arrow" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-1" /></span>@endif
        </a>
    @endforeach
</div>
