{{-- A repeating list inside a section (cards, steps, badges...). Alpine adds / removes / reorders rows; the server re-numbers them on save. --}}
@php
    $n = $f['name'];
    $rows = collect(old('data.'.$n, $data[$n] ?? []))->values()->map(fn ($r, $i) => (array) $r + ['_k' => $i + 1])->all();
    $blank = [];
    foreach ($f['fields'] as $sf) {
        $blank[$sf['name']] = $sf['type'] === 'checkbox' ? false : ($sf['type'] === 'select' ? array_key_first($sf['options']) : '');
        if ($sf['type'] === 'image') { $blank[$sf['name'].'_alt'] = ''; }
    }
    $base = rtrim(asset('/'), '/');
@endphp
<fieldset class="rounded-lg border border-sand-200 p-4" x-data="{ items: @js($rows), next: {{ count($rows) + 1 }}, max: {{ $f['max'] }}, blank: @js($blank),
    add() { if (this.items.length < this.max) this.items.push({ ...this.blank, _k: this.next++ }) },
    remove(i) { this.items.splice(i, 1) },
    up(i) { if (i > 0) this.items.splice(i - 1, 2, this.items[i], this.items[i - 1]) },
    down(i) { if (i < this.items.length - 1) this.items.splice(i, 2, this.items[i + 1], this.items[i]) } }">
    <legend class="field-label px-1">{{ $f['label'] }} <span class="font-normal text-ink/60">(up to {{ $f['max'] }})</span></legend>
    <p x-show="items.length === 0" x-cloak class="text-sm text-ink/60">Nothing here yet.</p>
    <div class="space-y-3">
        <template x-for="(it, i) in items" :key="it._k">
            <div class="rounded-lg border border-sand-200 bg-sand/40 p-3">
                <div class="mb-2 flex items-center justify-between">
                    <p class="label-caps text-xs text-rust">{{ ucfirst($f['item']) }} <span x-text="i + 1"></span></p>
                    <div class="flex gap-1 text-sm">
                        <button type="button" class="px-2" @click="up(i)" :disabled="i === 0" aria-label="Move up">&uarr;</button>
                        <button type="button" class="px-2" @click="down(i)" :disabled="i === items.length - 1" aria-label="Move down">&darr;</button>
                        <button type="button" class="px-2 text-rust underline" @click="remove(i)">Remove</button>
                    </div>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($f['fields'] as $sf)
                        @php $sn = $sf['name']; $span = in_array($sf['type'], ['textarea', 'image'], true) ? 'sm:col-span-2' : ''; @endphp
                        <div class="{{ $span }}">
                            @if ($sf['type'] === 'checkbox')
                                <input type="hidden" :name="`data[{{ $n }}][${i}][{{ $sn }}]`" value="0">
                                <label class="flex items-center gap-2 text-sm font-semibold"><input type="checkbox" :name="`data[{{ $n }}][${i}][{{ $sn }}]`" value="1" x-model="it.{{ $sn }}"> {{ $sf['label'] }}</label>
                            @elseif ($sf['type'] === 'image')
                                <label class="field-label">{{ $sf['label'] }}</label>
                                <div class="grid gap-3 sm:grid-cols-[6rem_1fr]">
                                    <div>
                                        <img x-show="it.{{ $sn }}" :src="'{{ $base }}/' + it.{{ $sn }}" alt="" class="h-20 w-24 rounded-lg object-cover">
                                        <p x-show="!it.{{ $sn }}" class="flex h-20 w-24 items-center justify-center rounded-lg bg-sand text-xs text-ink/60">No photo</p>
                                    </div>
                                    <div class="space-y-2">
                                        <input type="hidden" :name="`data[{{ $n }}][${i}][{{ $sn }}]`" :value="it.{{ $sn }}">
                                        <input type="file" :name="`data[{{ $n }}][${i}][{{ $sn }}_file]`" accept="image/jpeg,image/png,image/webp" class="field" aria-label="Upload photo">
                                        <input type="text" :name="`data[{{ $n }}][${i}][{{ $sn }}_alt]`" x-model="it.{{ $sn }}_alt" class="field" placeholder="Describe the photo (alt text)" aria-label="Alt text" maxlength="190">
                                        <button type="button" x-show="it.{{ $sn }}" class="text-xs text-rust underline" @click="it.{{ $sn }} = ''">Remove this photo</button>
                                    </div>
                                </div>
                            @else
                                <label class="field-label">{{ $sf['label'] }}</label>
                                @if ($sf['type'] === 'textarea')
                                    <textarea :name="`data[{{ $n }}][${i}][{{ $sn }}]`" x-model="it.{{ $sn }}" rows="3" class="field" maxlength="2000"></textarea>
                                @elseif ($sf['type'] === 'select')
                                    <select :name="`data[{{ $n }}][${i}][{{ $sn }}]`" x-model="it.{{ $sn }}" class="field">@foreach ($sf['options'] as $ov => $ol)<option value="{{ $ov }}">{{ $ol }}</option>@endforeach</select>
                                @else
                                    <input type="text" :name="`data[{{ $n }}][${i}][{{ $sn }}]`" x-model="it.{{ $sn }}" class="field" maxlength="{{ $sf['type'] === 'link' ? 255 : 300 }}" @if ($sf['type'] === 'link') placeholder="/page-name or https://... (blank = quote pop-up)" @endif>
                                @endif
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </template>
    </div>
    <button type="button" class="btn btn-outline mt-3 !py-2 text-ink" @click="add()" :disabled="items.length >= max" x-text="items.length >= max ? 'Limit reached' : '+ Add {{ $f['item'] }}'"></button>
</fieldset>
