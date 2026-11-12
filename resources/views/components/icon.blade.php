@props(['name', 'class' => 'h-6 w-6'])
{{-- Inline stroke icons. Add a new @case to add an icon; the quiz editor stores the icon name. --}}
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
@switch($name)
    @case('city') <path d="M3 21V9l5-3v15M8 21V4l7 3v14M15 21V11l6 2v8M3 21h18"/> @break
    @case('tulip') <path d="M12 21v-8M8 5c0 4 1.5 7 4 8 2.5-1 4-4 4-8l-2.2 2L12 4 10.2 7 8 5ZM12 17c-2 0-3.500-.8-4.500-2M12 17c2 0 3.500-.8 4.500-2"/> @break
    @case('spring') <path d="M12 21v-9M12 12c-3 0-5-2-5-5 3 0 5 2 5 5ZM12 12c3 0 5-2 5-5-3 0-5 2-5 5Z"/> @break
    @case('summer') <circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M2 12h2M20 12h2M4.900 4.900l1.400 1.400M17.700 17.700l1.400 1.400M4.900 19.100l1.400-1.400M17.700 6.300l1.400-1.400"/> @break
    @case('autumn') <path d="M12 21v-6M12 3c4 2 7 5 6 9-1 3-4 4-6 3-2 1-5 0-6-3-1-4 2-7 6-9Z"/> @break
    @case('winter') <path d="M12 2v20M4 7l16 10M20 7 4 17M9 4l3 2 3-2M9 20l3-2 3 2"/> @break
    @case('coach') <rect x="3" y="4" width="18" height="13" rx="2"/><path d="M3 11h18M7 17v2M17 17v2"/><circle cx="7.500" cy="14" r=".8"/><circle cx="16.500" cy="14" r=".8"/> @break
    @case('car') <path d="M5 16l1.500-6h11L19 16M3 16h18v3H3zM7 19v1.500M17 19v1.500"/><circle cx="7.500" cy="16" r=".8"/><circle cx="16.500" cy="16" r=".8"/> @break
    @case('guide') <circle cx="9" cy="7" r="3"/><path d="M3 21v-2a5 5 0 0 1 5-5h2M15 13l2 2 4-4"/> @break
    @case('question') <circle cx="12" cy="12" r="9"/><path d="M9.500 9a2.500 2.500 0 1 1 3.500 2.300c-.7.400-1 1-1 1.700M12 17h.01"/> @break
    @case('yes') <circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/> @break
    @case('no') <circle cx="12" cy="12" r="9"/><path d="m9 9 6 6M15 9l-6 6"/> @break
    @case('money') <circle cx="12" cy="12" r="9"/><path d="M14.500 9.500c-.5-1-1.500-1.500-2.500-1.500-1.500 0-2.500.8-2.500 2s1 1.700 2.500 2 2.500.8 2.500 2-1 2-2.500 2c-1 0-2-.5-2.500-1.500M12 6.500V8M12 16v1.500"/> @break
    @case('check') <path d="m5 12 5 5 9-10"/> @break
    @case('search') <circle cx="11" cy="11" r="7"/><path d="m20 20-3.500-3.500"/> @break
    @case('lock') <rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/> @break
    @case('phone') <path d="M5 4h4l2 5-2.500 1.500a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2Z"/> @break
    @case('mail') <rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/> @break
    @case('star') <path d="m12 3 2.700 5.600 6.100.8-4.500 4.200 1.100 6.100L12 16.800 6.600 19.700l1.100-6.100L3.200 9.400l6.100-.8L12 3Z"/> @break
    @case('shield') <path d="M12 3 5 6v5c0 5 3 8 7 10 4-2 7-5 7-10V6l-7-3Z"/><path d="m9 12 2 2 4-4"/> @break
    @case('map') <path d="M9 4 3 6v14l6-2 6 2 6-2V4l-6 2-6-2ZM9 4v14M15 6v14"/> @break
    @case('heart') <path d="M12 20s-8-5-8-11a4.500 4.500 0 0 1 8-2.700A4.500 4.500 0 0 1 20 9c0 6-8 11-8 11Z"/> @break
    @case('menu') <path d="M4 7h16M4 12h16M4 17h16"/> @break
    @case('close') <path d="M6 6l12 12M18 6 6 18"/> @break
    @case('chevron') <path d="m6 9 6 6 6-6"/> @break
    @case('arrow') <path d="M5 12h14m-6-6 6 6-6 6"/> @break
    @default <circle cx="12" cy="12" r="3"/>
@endswitch
</svg>
