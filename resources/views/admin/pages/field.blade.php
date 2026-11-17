{{-- One top-level section field. Expects $f (schema) and $data (resolved values). Input names: data[name], data[name_alt], data[name_file]. --}}
@php
    $n = $f['name'];
    $id = 'f-'.$n;
    $v = old('data.'.$n, $data[$n] ?? '');
@endphp
<div>
    @switch($f['type'])
        @case('checkbox')
            <input type="hidden" name="data[{{ $n }}]" value="0">
            <label class="flex items-center gap-2 text-sm font-semibold"><input type="checkbox" name="data[{{ $n }}]" value="1" @checked($v)> {{ $f['label'] }}</label>
            @break
        @case('textarea')
            <label class="field-label" for="{{ $id }}">{{ $f['label'] }}</label>
            <textarea id="{{ $id }}" name="data[{{ $n }}]" rows="4" class="field" maxlength="2000">{{ $v }}</textarea>
            @break
        @case('rich')
            <label class="field-label" for="{{ $id }}">{{ $f['label'] }}</label>
            <input id="{{ $id }}" type="hidden" name="data[{{ $n }}]" value="{{ $v }}"><trix-editor input="{{ $id }}"></trix-editor>
            @break
        @case('select')
            <label class="field-label" for="{{ $id }}">{{ $f['label'] }}</label>
            <select id="{{ $id }}" name="data[{{ $n }}]" class="field max-w-md">@foreach ($f['options'] as $ov => $ol)<option value="{{ $ov }}" @selected((string) $v === (string) $ov)>{{ $ol }}</option>@endforeach</select>
            @break
        @case('number')
            <label class="field-label" for="{{ $id }}">{{ $f['label'] }}</label>
            <input id="{{ $id }}" type="number" min="0" max="999" name="data[{{ $n }}]" value="{{ $v }}" class="field max-w-[8rem]">
            @break
        @case('image')
            <label class="field-label" for="{{ $id }}">{{ $f['label'] }}</label>
            <div class="grid gap-3 rounded-lg border border-sand-200 p-3 sm:grid-cols-[8rem_1fr]">
                <div>
                    @if ($v)<img src="{{ asset($v) }}" alt="" class="h-24 w-32 rounded-lg object-cover" width="128" height="96">@else<p class="flex h-24 w-32 items-center justify-center rounded-lg bg-sand text-xs text-ink/60">No photo</p>@endif
                </div>
                <div class="space-y-2">
                    <input type="hidden" name="data[{{ $n }}]" value="{{ $v }}">
                    <input id="{{ $id }}" type="file" name="data[{{ $n }}_file]" accept="image/jpeg,image/png,image/webp" class="field" aria-label="{{ $v ? 'Replace photo' : 'Upload photo' }}">
                    <input type="text" name="data[{{ $n }}_alt]" value="{{ old('data.'.$n.'_alt', $data[$n.'_alt'] ?? '') }}" class="field" placeholder="Describe the photo (alt text, for accessibility and SEO)" aria-label="Alt text" maxlength="190">
                    @if ($v)<label class="flex items-center gap-2 text-xs"><input type="checkbox" name="data[{{ $n }}_remove]" value="1"> Remove this photo</label>@endif
                </div>
            </div>
            @break
        @default {{-- text, link --}}
            <label class="field-label" for="{{ $id }}">{{ $f['label'] }}</label>
            <input id="{{ $id }}" type="text" name="data[{{ $n }}]" value="{{ $v }}" class="field" maxlength="{{ $f['type'] === 'link' ? 255 : 300 }}" @if ($f['type'] === 'link') placeholder="/page-name or https://..." @endif>
    @endswitch
    @if (! empty($f['help']) && $f['type'] !== 'checkbox')<p class="mt-1 text-xs text-ink/60">{{ $f['help'] }}</p>@endif
</div>
