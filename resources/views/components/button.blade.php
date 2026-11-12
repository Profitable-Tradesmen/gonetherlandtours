@props(['href' => null, 'variant' => 'primary', 'block' => false, 'type' => 'button'])
@php
    $classes = 'btn btn-'.$variant.($block ? ' btn-block' : '');
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
