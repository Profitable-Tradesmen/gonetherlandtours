{{-- A main menu item with a mega menu: the name is a link to its overview page, the arrow opens the panel (for keyboard and touch);
     hovering either one opens it too. Expects $key, $panel (panel id), $href, $label. --}}
<span class="flex items-center rounded-lg transition-colors" :class="dd === '{{ $key }}' && 'bg-sand'" @mouseenter="dd = '{{ $key }}'">
    <a href="{{ $href }}" class="hdr-link inline-flex items-center whitespace-nowrap rounded-l-lg py-2.5 pl-2 pr-0.5 text-[0.9rem] font-medium text-ink transition-colors hover:bg-sand xl:pl-3 2xl:pl-4">{{ $label }}</a>
    <button type="button" class="hdr-link inline-flex h-[2.625rem] w-6 items-center justify-center rounded-r-lg text-ink transition-colors hover:bg-sand xl:mr-1 2xl:mr-2" @click="dd = dd === '{{ $key }}' ? null : '{{ $key }}'" :aria-expanded="dd === '{{ $key }}'" aria-controls="{{ $panel }}" aria-label="{{ $label }} menu">
        <x-icon name="chevron" class="h-4 w-4 transition-transform" ::class="dd === '{{ $key }}' && 'rotate-180'" />
    </button>
</span>
