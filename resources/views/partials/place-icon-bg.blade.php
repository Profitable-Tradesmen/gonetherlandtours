@props(['slug' => null])
{{--
    Faint line-art landmark used as a card background (decorative; hidden from assistive tech). One icon per destination:
    tulips-and-countryside = a windmill, amsterdam-and-the-cities = a gabled canal house, zeeland-and-the-delta = a lighthouse; anything else = a windmill.
    Put it as the first child of a `relative isolate overflow-hidden` box.
--}}
<svg viewBox="0 0 200 200" class="pointer-events-none absolute -bottom-5 -right-5 -z-10 h-[11rem] w-[11rem] text-brand-700 opacity-[0.09]" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
    @if ($slug === 'amsterdam-and-the-cities')
        {{-- Gabled canal house with a hoist beam, between two neighbors --}}
        <path d="M14 200V112h12V100h12V88h12v112M150 200V88h12v12h12v12h12v88"/>
        <path d="M52 200V92c0-16 20-14 20-30 0-14 14-16 28-40 14 24 28 26 28 40 0 16 20 14 20 30v108"/>
        <path d="M100 22v-8M94 14h12"/>
        <path d="M100 22v22"/><circle cx="100" cy="48" r="3"/>
        <rect x="68" y="104" width="18" height="26" rx="9"/><rect x="114" y="104" width="18" height="26" rx="9"/>
        <rect x="68" y="146" width="18" height="26" rx="9"/><rect x="114" y="146" width="18" height="26" rx="9"/>
        <path d="M88 200v-28a12 12 0 0 1 24 0v28"/>
        <path d="M0 200h200"/>
    @elseif ($slug === 'zeeland-and-the-delta')
        {{-- Lighthouse on the dunes, with waves --}}
        <path d="M78 176 88 78h24l10 98Z"/>
        <path d="M84 116h32M81 146h38"/>
        <path d="M82 78h36v8H82z"/>
        <rect x="90" y="52" width="20" height="26"/>
        <path d="M86 52 100 30l14 22Z"/><path d="M100 30v-10"/>
        <path d="M70 62 54 54M70 70 50 70M130 62l16-8M130 70l20 0"/>
        <path d="M94 176v-16a6 6 0 0 1 12 0v16"/>
        <path d="M10 188q10-8 20 0t20 0 20 0 20 0 20 0 20 0 20 0 20 0 20 0M0 200h200"/>
    @else
        {{-- Dutch windmill: tapered tower, cap, four lattice sails --}}
        <path d="M76 200 90 100h20l14 100Z"/>
        <path d="M88 100c0-10 6-14 12-14s12 4 12 14"/>
        <circle cx="100" cy="90" r="4"/>
        <path d="M86 150h28"/>
        <path d="M92 200v-22a8 8 0 0 1 16 0v22"/>
        @foreach ([45, 135, 225, 315] as $deg)
            <g transform="rotate({{ $deg }} 100 90)"><path d="M100 86V8"/><rect x="100" y="12" width="17" height="46"/><path d="M100 24h17M100 36h17M100 48h17M108.5 12v46"/></g>
        @endforeach
        <path d="M0 200h200"/>
    @endif
</svg>
