@extends('admin.layout')
@section('title', $step->exists ? 'Edit quiz step' : 'Add quiz step')
@php
    $opts = old('options', $step->exists ? $step->options->map(fn ($o) => ['label' => $o->label, 'value' => $o->value, 'icon' => $o->icon ?? '', 'active' => (bool) $o->is_active])->all() : [['label' => '', 'value' => '', 'icon' => '', 'active' => true], ['label' => '', 'value' => '', 'icon' => '', 'active' => true]]);
@endphp
@section('content')
<h1 class="font-display text-3xl">{{ $step->exists ? 'Edit quiz step' : 'Add quiz step' }}</h1>
<form method="POST" action="{{ $step->exists ? route('admin.quiz.update', $step) : route('admin.quiz.store') }}" class="mt-6 max-w-3xl space-y-5 rounded-xl bg-white p-5 shadow-sm sm:p-7" x-data="{ rows: @js($opts) }">
    @csrf @if ($step->exists) @method('PUT') @endif
    <div><label class="field-label" for="question">Question *</label><input id="question" name="question" value="{{ old('question', $step->question) }}" class="field" required></div>
    <div class="grid gap-4 sm:grid-cols-2">
        <div><label class="field-label" for="key">Key (answer name, e.g. group_size)</label>
            @if ($step->exists)<input id="key" value="{{ $step->key }}" class="field bg-sand" disabled>@else<input id="key" name="key" value="{{ old('key') }}" class="field" required placeholder="lowercase_with_underscores">@endif</div>
        <div><label class="field-label" for="type">Type</label><select id="type" name="type" class="field"><option value="tiles" @selected(old('type', $step->type) === 'tiles')>Tiles (click advances)</option><option value="select" @selected(old('type', $step->type) === 'select')>Dropdown (Next button)</option></select></div>
    </div>
    <label class="flex items-center gap-2 text-sm font-semibold"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $step->is_active))> Active</label>

    <div><h2 class="font-display text-xl">Options (in order)</h2>
        <template x-for="(r, i) in rows" :key="i"><div class="mt-2 grid items-center gap-2 rounded-lg border border-sand-200 p-3 sm:grid-cols-[1fr_1fr_9rem_auto]">
            <input class="field" :name="'options[' + i + '][label]'" x-model="r.label" placeholder="Label shown" aria-label="Label" required>
            <input class="field" :name="'options[' + i + '][value]'" x-model="r.value" placeholder="Saved value (blank = label)" aria-label="Value">
            <select class="field" :name="'options[' + i + '][icon]'" x-model="r.icon" aria-label="Icon"><option value="">No icon</option>@foreach ($icons as $ic)<option>{{ $ic }}</option>@endforeach</select>
            <div class="flex items-center gap-2 text-sm"><label class="flex items-center gap-1"><input type="checkbox" :name="'options[' + i + '][active]'" value="1" x-model="r.active"> On</label>
                <button type="button" class="px-1" @click="if (i > 0) { rows.splice(i - 1, 2, rows[i], rows[i - 1]) }" aria-label="Move up">&uarr;</button>
                <button type="button" class="px-1" @click="if (i < rows.length - 1) { rows.splice(i, 2, rows[i + 1], rows[i]) }" aria-label="Move down">&darr;</button>
                <button type="button" class="px-1 text-rust" @click="rows.splice(i, 1)" aria-label="Remove">&times;</button></div>
        </div></template>
        <button type="button" class="mt-3 text-sm font-semibold text-brand-700 underline" @click="rows.push({label: '', value: '', icon: '', active: true})">+ Add option</button>
    </div>
    <div class="flex gap-3"><x-button type="submit" variant="secondary">Save step</x-button><x-button :href="route('admin.quiz.index')" variant="outline" class="text-ink">Cancel</x-button></div>
</form>
@endsection
