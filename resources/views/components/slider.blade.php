@props(['tag' => 'div', 'light' => false])
{{-- On phones the children become a swipe slider with dots; from sm up the classes you pass (grid...) apply as normal.
     Each dot is a 24px-tall tap target (min 24x24, WCAG 2.2 / Lighthouse target-size) around a smaller visible dot. --}}
<div x-data="slider" class="min-w-0">
    <{{ $tag }} x-ref="track" @scroll.passive="sync()" {{ $attributes->merge(['class' => 'slider']) }}>{{ $slot }}</{{ $tag }}>
    <div class="flex justify-center sm:hidden" x-show="n > 1" x-cloak role="tablist" aria-label="Slides">
        <template x-for="k in n" :key="k">
            <button type="button" role="tab" :aria-selected="i === k - 1" :aria-label="'Go to slide ' + k" @click="go(k - 1)" class="flex h-6 min-w-6 items-center justify-center px-1">
                <span class="block h-2 rounded-full transition-all" :class="[i === k - 1 ? 'w-6 {{ $light ? 'bg-accent' : 'bg-brand-700' }}' : 'w-2 {{ $light ? 'bg-white/40' : 'bg-brand-700/25' }}']"></span>
            </button>
        </template>
    </div>
</div>
