@props(['name', 'label', 'idp', 'type' => 'text', 'required' => false, 'autocomplete' => null, 'hint' => null, 'span' => false])
{{--
    One labelled field with inline error (Alpine err() for AJAX, old()/$errors for no-JS posts).
    Use inside <x-enquiry-form>. Pass a <select> or <textarea> via the slot instead of type.
--}}
@php
    $id = $idp.'-'.$name;
    $serverError = old('_form_id') === $idp ? $errors->first($name) : '';
@endphp
<div @class(['sm:col-span-2' => $span])>
    <label for="{{ $id }}" class="field-label">{{ $label }}@if ($required) <span class="text-rust" aria-hidden="true">*</span><span class="sr-only">(required)</span>@endif</label>
    @if ($slot->isNotEmpty())
        {{ $slot }}
    @else
        <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" class="field" @required($required)
               @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
               value="{{ old('_form_id') === $idp ? old($name) : '' }}"
               :aria-invalid="err('{{ $name }}') ? 'true' : 'false'"
               aria-describedby="{{ $id }}-error">
    @endif
    @if ($hint)<p class="mt-1 text-xs text-ink/60">{{ $hint }}</p>@endif
    <p id="{{ $id }}-error" class="field-error" x-show="err('{{ $name }}')" x-text="err('{{ $name }}')" role="alert">{{ $serverError }}</p>
    @if ($serverError)<noscript><p class="field-error">{{ $serverError }}</p></noscript>@endif
</div>
