@extends('admin.layout')
@section('title', $snippet->exists ? 'Edit snippet' : 'Add snippet')
@php
    $known = array_keys($choices);
    $incAll = \App\Models\CodeSnippet::parsePaths($snippet->include_paths);
    $excAll = \App\Models\CodeSnippet::parsePaths($snippet->exclude_paths);
    $incPicked = old('include_pages', array_values(array_intersect($incAll, $known)));
    $excPicked = old('exclude_pages', array_values(array_intersect($excAll, $known)));
    $incExtra = old('include_extra', implode("\n", array_diff($incAll, $known)));
    $excExtra = old('exclude_extra', implode("\n", array_diff($excAll, $known)));
@endphp
@section('content')
<h1 class="font-display text-3xl">{{ $snippet->exists ? 'Edit snippet' : 'Add snippet' }}</h1>
<div class="mt-3 max-w-3xl rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900" role="note">
    <p class="font-semibold">Careful: invalid code can break your site.</p>
    <p class="mt-1">The code is output exactly as you type it. Test on one page first, and disable the snippet if something looks wrong.</p>
</div>

<form method="POST" action="{{ $snippet->exists ? route('admin.snippets.update', $snippet->id) : route('admin.snippets.store') }}" class="mt-5 max-w-3xl space-y-5 rounded-xl bg-white p-5 shadow-sm sm:p-7" x-data="{ scope: @js(old('scope', $snippet->scope)) }">
    @csrf @if ($snippet->exists) @method('PUT') @endif

    <div><label class="field-label" for="name">Name *</label><input id="name" name="name" value="{{ old('name', $snippet->name) }}" class="field" required maxlength="120" placeholder="e.g. Facebook Pixel"></div>

    <div>
        <label class="field-label" for="code">Code *</label>
        <textarea id="code" name="code" rows="12" class="field font-mono text-xs leading-relaxed" required spellcheck="false" autocomplete="off" placeholder="&lt;script&gt;...&lt;/script&gt;  or  &lt;style&gt;...&lt;/style&gt;  or any HTML">{{ old('code', $snippet->code) }}</textarea>
        <p class="mt-1 text-xs text-ink/60">Include the tags yourself (<code>&lt;script&gt;</code>, <code>&lt;style&gt;</code>, <code>&lt;img&gt;</code>...). It is saved and output exactly as entered.</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div><label class="field-label" for="position">Placement *</label>
            <select id="position" name="position" class="field">@foreach (\App\Models\CodeSnippet::POSITIONS as $k => $label)<option value="{{ $k }}" @selected(old('position', $snippet->position) === $k)>{{ $label }}</option>@endforeach</select></div>
        <div><label class="field-label" for="sort_order">Order (lowest loads first)</label><input id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $snippet->sort_order) }}" class="field"></div>
    </div>

    <fieldset>
        <legend class="field-label">Where should it load? *</legend>
        <label class="mt-1 flex items-center gap-2 text-sm"><input type="radio" name="scope" value="all" x-model="scope"> Entire website</label>
        <label class="mt-1 flex items-center gap-2 text-sm"><input type="radio" name="scope" value="only" x-model="scope"> Specific pages only</label>

        <div x-show="scope === 'only'" x-cloak class="mt-3 rounded-lg border border-sand-200 p-4">
            <p class="text-sm font-semibold">Choose pages</p>
            <div class="mt-2 grid max-h-56 gap-1 overflow-y-auto sm:grid-cols-2">
                @foreach ($choices as $path => $label)
                    <label class="flex items-start gap-2 text-sm"><input type="checkbox" name="include_pages[]" value="{{ $path }}" class="mt-1" @checked(in_array($path, (array) $incPicked, true))> <span>{{ $label }} <span class="text-xs text-ink/50">{{ $path }}</span></span></label>
                @endforeach
            </div>
            <label class="field-label mt-4" for="include_extra">Other addresses (optional)</label>
            <textarea id="include_extra" name="include_extra" rows="3" class="field font-mono text-xs" placeholder="One per line: /my-page, my-slug, https://yoursite.com/page, /tours/*">{{ $incExtra }}</textarea>
            <p class="mt-1 text-xs text-ink/60">A page address, a slug or a full URL. End with <code>*</code> to match everything below it (for example <code>/blog/*</code>).</p>
        </div>
    </fieldset>

    <details class="rounded-lg border border-sand-200 p-4" @if ($excPicked || $excExtra) open @endif>
        <summary class="cursor-pointer text-sm font-semibold">Exclude pages (optional)</summary>
        <p class="mt-2 text-xs text-ink/60">The snippet never loads on these pages, even when the scope above includes them.</p>
        <div class="mt-2 grid max-h-56 gap-1 overflow-y-auto sm:grid-cols-2">
            @foreach ($choices as $path => $label)
                <label class="flex items-start gap-2 text-sm"><input type="checkbox" name="exclude_pages[]" value="{{ $path }}" class="mt-1" @checked(in_array($path, (array) $excPicked, true))> <span>{{ $label }} <span class="text-xs text-ink/50">{{ $path }}</span></span></label>
            @endforeach
        </div>
        <label class="field-label mt-4" for="exclude_extra">Other addresses to exclude</label>
        <textarea id="exclude_extra" name="exclude_extra" rows="2" class="field font-mono text-xs" placeholder="One per line">{{ $excExtra }}</textarea>
    </details>

    <label class="flex items-center gap-2 text-sm font-semibold"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $snippet->is_active))> Enabled</label>

    <div class="flex flex-wrap gap-3"><x-button type="submit" variant="secondary">Save snippet</x-button><x-button :href="route('admin.snippets.index')" variant="outline" class="text-ink">Cancel</x-button></div>
</form>
@endsection
