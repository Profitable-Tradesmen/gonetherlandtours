@extends('admin.layout')
@section('title', ($item->exists ? 'Edit ' : 'Add ').strtolower($cfg['title']))
@section('content')
<h1 class="font-display text-3xl">{{ $item->exists ? 'Edit' : 'Add' }} {{ strtolower($cfg['title']) }}</h1>
<form method="POST" enctype="multipart/form-data" action="{{ $item->exists ? route($cfg['route'].'.update', $item->id) : route($cfg['route'].'.store') }}" class="mt-6 max-w-3xl space-y-5 rounded-xl bg-white p-5 shadow-sm sm:p-7">
    @csrf
    @if ($item->exists) @method('PUT') @endif
    @foreach ($cfg['fields'] as $f)
        @php $n = $f['name']; $val = old($n, $item->{$n} ?? ($f['type'] === 'checkbox' && ! $item->exists ? true : null)); @endphp
        <div>
            @if ($f['type'] === 'checkbox')
                <label class="flex items-center gap-2 text-sm font-semibold"><input type="checkbox" name="{{ $n }}" value="1" @checked($val)> {{ $f['label'] }}</label>
            @elseif ($f['type'] === 'image')
                <label class="field-label" for="f-{{ $n }}">{{ $f['label'] }}</label>
                @if ($item->{$n})<img src="{{ asset($item->{$n}) }}" alt="" class="mb-2 h-24 rounded-lg object-cover" width="160" height="107">@endif
                <input id="f-{{ $n }}" type="file" name="{{ $n }}_file" accept="image/jpeg,image/png,image/webp" class="field">
                <label class="field-label mt-2" for="f-{{ $n }}_alt">Alt text (required when uploading)</label>
                <input id="f-{{ $n }}_alt" type="text" name="{{ $n }}_alt" value="{{ old($n.'_alt', $item->{$n.'_alt'}) }}" class="field">
            @else
                <label class="field-label" for="f-{{ $n }}">{{ $f['label'] }}</label>
                @if ($f['type'] === 'textarea')
                    <textarea id="f-{{ $n }}" name="{{ $n }}" rows="4" class="field">{{ $val }}</textarea>
                @elseif ($f['type'] === 'rich')
                    <input id="f-{{ $n }}" type="hidden" name="{{ $n }}" value="{{ $val }}"><trix-editor input="f-{{ $n }}"></trix-editor>
                @elseif ($f['type'] === 'password')
                    <input id="f-{{ $n }}" type="password" name="{{ $n }}" autocomplete="new-password" class="field">
                @else
                    <input id="f-{{ $n }}" type="{{ $f['type'] === 'number' ? 'number' : 'text' }}" name="{{ $n }}" value="{{ $val }}" class="field">
                @endif
            @endif
        </div>
    @endforeach
    <div class="flex gap-3"><x-button type="submit" variant="secondary">Save</x-button><x-button :href="route($cfg['route'].'.index')" variant="outline" class="text-ink">Cancel</x-button></div>
</form>
@endsection
