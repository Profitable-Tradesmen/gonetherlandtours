@extends('admin.layout')
@section('title', $package->exists ? 'Edit package' : 'New package')
@php
    $v = fn ($k, $d = null) => old($k, $package->{$k} ?? $d);
    $lines = fn ($k) => old($k.'_text', implode("\n", $package->{$k} ?? []));
@endphp
@section('content')
<h1 class="font-display text-3xl">{{ $package->exists ? 'Edit package' : 'New package' }}</h1>
@if ($package->exists)<p class="mt-1 text-sm"><a class="underline" target="_blank" href="{{ route('packages.show', $package) }}">View on site &nearr;</a> (drafts show a 404 to visitors)</p>@endif
<form method="POST" enctype="multipart/form-data" action="{{ $package->exists ? route('admin.packages.update', $package) : route('admin.packages.store') }}" class="mt-6 space-y-6">
    @csrf @if ($package->exists) @method('PUT') @endif

    <section class="space-y-4 rounded-xl bg-white p-5 shadow-sm sm:p-7"><h2 class="font-display text-xl">Basics</h2>
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2"><label class="field-label" for="title">Title *</label><input id="title" name="title" value="{{ $v('title') }}" class="field" required></div>
            <div><label class="field-label" for="slug">URL slug (blank = from title)</label><input id="slug" name="slug" value="{{ $v('slug') }}" class="field"></div>
            <div><label class="field-label" for="nights">Nights *</label><input id="nights" type="number" min="1" name="nights" value="{{ $v('nights', 7) }}" class="field" required></div>
            <div><label class="field-label" for="tour_type_id">Tour type</label><select id="tour_type_id" name="tour_type_id" class="field"><option value="">None</option>@foreach ($tourTypes as $t)<option value="{{ $t->id }}" @selected($v('tour_type_id') == $t->id)>{{ $t->name }}</option>@endforeach</select></div>
            <div><label class="field-label" for="destination_id">Destination</label><select id="destination_id" name="destination_id" class="field"><option value="">None</option>@foreach ($destinations as $d)<option value="{{ $d->id }}" @selected($v('destination_id') == $d->id)>{{ $d->name }}</option>@endforeach</select></div>
            <div><label class="field-label" for="price_from">"From" price per person (blank = [CONFIRM: price])</label><input id="price_from" type="number" step="0.01" min="0" name="price_from" value="{{ $v('price_from') }}" class="field"></div>
            <div><label class="field-label" for="currency">Currency</label><select id="currency" name="currency" class="field">@foreach (['EUR', 'USD', 'GBP'] as $c)<option @selected($v('currency', 'EUR') === $c)>{{ $c }}</option>@endforeach</select></div>
            <div class="sm:col-span-2"><label class="field-label" for="summary">Short summary</label><textarea id="summary" name="summary" rows="2" class="field">{{ $v('summary') }}</textarea></div>
        </div>
        <div class="flex flex-wrap gap-5 text-sm font-semibold">
            <label class="flex items-center gap-2"><input type="checkbox" name="is_published" value="1" @checked($v('is_published'))> Published</label>
            <label class="flex items-center gap-2"><input type="checkbox" name="is_featured" value="1" @checked($v('is_featured'))> Featured on home page</label>
            <label class="flex items-center gap-2"><input type="checkbox" name="is_sample" value="1" @checked($v('is_sample'))> Sample data</label>
            <label class="flex items-center gap-2"><input type="checkbox" name="noindex" value="1" @checked($v('noindex'))> Hide from search engines</label>
        </div>
    </section>

    <section class="space-y-4 rounded-xl bg-white p-5 shadow-sm sm:p-7"><h2 class="font-display text-xl">Content</h2>
        <div><label class="field-label" for="description">Full description</label><input id="description" type="hidden" name="description" value="{{ $v('description') }}"><trix-editor input="description"></trix-editor></div>
        <div class="grid gap-4 sm:grid-cols-3">
            <div><label class="field-label" for="highlights_text">Highlights (one per line)</label><textarea id="highlights_text" name="highlights_text" rows="6" class="field">{{ $lines('highlights') }}</textarea></div>
            <div><label class="field-label" for="inclusions_text">Included (one per line)</label><textarea id="inclusions_text" name="inclusions_text" rows="6" class="field">{{ $lines('inclusions') }}</textarea></div>
            <div><label class="field-label" for="exclusions_text">Not included (one per line)</label><textarea id="exclusions_text" name="exclusions_text" rows="6" class="field">{{ $lines('exclusions') }}</textarea></div>
        </div>
    </section>

    <section class="rounded-xl bg-white p-5 shadow-sm sm:p-7" x-data="{ rows: @js(old('itinerary', $package->itinerary ?? [])) }"><h2 class="font-display text-xl">Day-by-day itinerary</h2>
        <template x-for="(row, i) in rows" :key="i"><div class="mt-3 rounded-lg border border-sand-200 p-3">
            <label class="field-label">Day title</label><input class="field" :name="'itinerary[' + i + '][title]'" x-model="row.title">
            <label class="field-label mt-2">Description</label><textarea rows="3" class="field" :name="'itinerary[' + i + '][body]'" x-model="row.body"></textarea>
            <button type="button" class="mt-2 text-sm text-rust underline" @click="rows.splice(i, 1)">Remove day</button></div></template>
        <button type="button" class="mt-3 text-sm font-semibold text-brand-700 underline" @click="rows.push({title: 'Day ' + (rows.length + 1) + ': ', body: ''})">+ Add day</button>
    </section>

    <section class="rounded-xl bg-white p-5 shadow-sm sm:p-7" x-data="{ rows: @js(old('faqs', $package->faqs ?? [])) }"><h2 class="font-display text-xl">Package FAQs</h2>
        <template x-for="(row, i) in rows" :key="i"><div class="mt-3 rounded-lg border border-sand-200 p-3">
            <label class="field-label">Question</label><input class="field" :name="'faqs[' + i + '][question]'" x-model="row.question">
            <label class="field-label mt-2">Answer</label><textarea rows="2" class="field" :name="'faqs[' + i + '][answer]'" x-model="row.answer"></textarea>
            <button type="button" class="mt-2 text-sm text-rust underline" @click="rows.splice(i, 1)">Remove</button></div></template>
        <button type="button" class="mt-3 text-sm font-semibold text-brand-700 underline" @click="rows.push({question: '', answer: ''})">+ Add FAQ</button>
    </section>

    <section class="space-y-4 rounded-xl bg-white p-5 shadow-sm sm:p-7"><h2 class="font-display text-xl">Gallery images</h2>
        <p class="text-sm text-ink/70">The first image is the cover. JPG, PNG or WebP up to 8 MB; they are resized and converted to WebP. Alt text is required.</p>
        @if ($package->exists)
            <div class="grid gap-3 sm:grid-cols-2">@foreach ($package->images as $img)
                <div class="flex gap-3 rounded-lg border border-sand-200 p-3">
                    <img src="{{ $img->url(480) }}" alt="" width="96" height="64" class="h-16 w-24 shrink-0 rounded object-cover">
                    <div class="min-w-0 flex-1 space-y-1">
                        <input class="field !py-1.5" name="img[{{ $img->id }}][alt]" value="{{ old('img.'.$img->id.'.alt', $img->alt) }}" aria-label="Alt text" required>
                        <div class="flex items-center gap-3 text-sm"><label>Order <input type="number" class="field !inline-block !w-20 !py-1" name="img[{{ $img->id }}][sort]" value="{{ $img->pivot->sort_order }}"></label>
                        <label class="flex items-center gap-1 text-rust"><input type="checkbox" name="remove[{{ $img->id }}]" value="1"> Remove</label></div>
                    </div></div>
            @endforeach</div>
        @endif
        <div x-data="{ n: 1 }">
            <template x-for="i in n" :key="i"><div class="mt-2 grid gap-2 sm:grid-cols-2"><input type="file" :name="'new_images[' + (i - 1) + ']'" accept="image/jpeg,image/png,image/webp" class="field" aria-label="New image"><input type="text" :name="'new_alts[' + (i - 1) + ']'" class="field" placeholder="Alt text (describe the image)" aria-label="Alt text for new image"></div></template>
            <button type="button" class="mt-2 text-sm font-semibold text-brand-700 underline" @click="n++">+ Add another image</button>
        </div>
    </section>

    <section class="space-y-4 rounded-xl bg-white p-5 shadow-sm sm:p-7"><h2 class="font-display text-xl">SEO</h2>
        <div><label class="field-label" for="seo_title">SEO title</label><input id="seo_title" name="seo_title" value="{{ $v('seo_title') }}" class="field" maxlength="190"></div>
        <div><label class="field-label" for="meta_description">Meta description (about 155 characters)</label><textarea id="meta_description" name="meta_description" rows="2" class="field" maxlength="320">{{ $v('meta_description') }}</textarea></div>
        <div><label class="field-label" for="social_image">Social image URL (blank = first gallery image)</label><input id="social_image" name="social_image" value="{{ $v('social_image') }}" class="field"></div>
    </section>

    <div class="flex gap-3"><x-button type="submit" variant="secondary">Save package</x-button><x-button :href="route('admin.packages.index')" variant="outline" class="text-ink">Back</x-button></div>
</form>
@endsection
