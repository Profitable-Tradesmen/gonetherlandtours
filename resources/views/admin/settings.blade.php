@extends('admin.layout')
@section('title', 'Settings')
@section('content')
<h1 class="font-display text-3xl">Site settings</h1>
<form method="POST" enctype="multipart/form-data" action="{{ route('admin.settings.update') }}" class="mt-6 max-w-3xl space-y-6">
    @csrf @method('PUT')
    @foreach ($groups as $group => $fields)
        <section class="space-y-4 rounded-xl bg-white p-5 shadow-sm sm:p-7"><h2 class="font-display text-xl">{{ $group }}</h2>
            @foreach ($fields as $key => [$label, $type, $help])
                <div><label class="field-label" for="s-{{ $key }}">{{ $label }}</label>
                    @if ($type === 'textarea')<textarea id="s-{{ $key }}" name="{{ $key }}" rows="3" class="field">{{ old($key, setting($key)) }}</textarea>
                    @elseif ($type === 'secret')<input id="s-{{ $key }}" type="password" name="{{ $key }}" class="field" autocomplete="off" placeholder="{{ setting($key) ? '(saved: leave blank to keep)' : '' }}">
                    @else<input id="s-{{ $key }}" name="{{ $key }}" value="{{ old($key, setting($key)) }}" class="field">@endif
                    @if ($help)<p class="mt-1 text-xs text-ink/60">{{ $help }}</p>@endif</div>
            @endforeach
        </section>
    @endforeach
    <section class="space-y-4 rounded-xl bg-white p-5 shadow-sm sm:p-7"><h2 class="font-display text-xl">Logo and images</h2>
        @foreach ($files as $key => $label)
            <div><label class="field-label" for="s-{{ $key }}">{{ $label }}</label>@if (setting($key))<img src="{{ asset(setting($key)) }}" alt="" class="mb-2 h-14 rounded bg-sand p-1">@endif<input id="s-{{ $key }}" type="file" name="{{ $key }}_file" accept="image/png,image/jpeg,image/webp" class="field"></div>
        @endforeach
    </section>
    <section class="rounded-xl bg-white p-5 shadow-sm sm:p-7"><h2 class="font-display text-xl">GoHighLevel</h2>
        <p class="mt-2 text-sm">Status: <strong>{{ ghl_enabled() ? 'ON (token and location ID found in .env)' : 'OFF (add GHL_API_TOKEN and GHL_LOCATION_ID to .env)' }}</strong></p>
        <p class="mt-1 text-xs text-ink/60">The token is never shown or stored here. See docs/reference/GHL_INTEGRATION.md.</p></section>
    <x-button type="submit" variant="secondary">Save settings</x-button>
</form>
@endsection
