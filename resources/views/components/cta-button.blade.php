@props(['label', 'url' => '', 'variant' => 'primary'])
{{-- A button whose link comes from the admin: blank link = open the quote pop-up, otherwise a normal link. --}}
@php $href = trim((string) $url); if ($href !== '' && str_starts_with($href, '/')) { $href = url($href); } @endphp
@if ($href !== '')
    <x-button :href="$href" :variant="$variant" {{ $attributes }}>{{ $label }}</x-button>
@else
    <x-button :variant="$variant" @click="$store.modal.show()" {{ $attributes }}>{{ $label }}</x-button>
@endif
