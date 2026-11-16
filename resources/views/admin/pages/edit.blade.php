@extends('admin.layout')
@section('title', 'Edit '.$page->title)
@php $c = fn ($k, $d = '') => old('c.'.$k, data_get($page->content, $k, $d)); @endphp
@section('content')
<h1 class="font-display text-3xl">Edit: {{ $page->title }}</h1>
<form method="POST" enctype="multipart/form-data" action="{{ route('admin.pages.update', $page) }}" class="mt-6 max-w-4xl space-y-6">
    @csrf @method('PUT')
    <section class="space-y-4 rounded-xl bg-white p-5 shadow-sm sm:p-7"><h2 class="font-display text-xl">Page basics and SEO</h2>
        <div class="grid gap-4 sm:grid-cols-2">
            <div><label class="field-label" for="title">Page name *</label><input id="title" name="title" value="{{ old('title', $page->title) }}" class="field" required></div>
            <div><label class="field-label" for="h1">Main heading (H1)</label><input id="h1" name="h1" value="{{ old('h1', $page->h1) }}" class="field"></div>
        </div>
        <div><label class="field-label" for="seo_title">SEO title</label><input id="seo_title" name="seo_title" value="{{ old('seo_title', $page->seo_title) }}" class="field" maxlength="190"></div>
        <div><label class="field-label" for="meta_description">Meta description (about 155 characters)</label><textarea id="meta_description" name="meta_description" rows="2" class="field" maxlength="320">{{ old('meta_description', $page->meta_description) }}</textarea></div>
        <label class="flex items-center gap-2 text-sm font-semibold"><input type="checkbox" name="noindex" value="1" @checked(old('noindex', $page->noindex))> Hide this page from search engines (noindex)</label>
        @if (in_array($page->slug, ['terms', 'privacy']))
            <div><label class="field-label" for="version">Version (stored with each enquiry as the terms version)</label><input id="version" name="version" value="{{ old('version', $page->version) }}" class="field max-w-xs"><p class="mt-1 text-xs text-ink/60">Increase this whenever you change the legal text.</p></div>
        @endif
    </section>

    @if ($page->slug === 'home')
        <section class="space-y-4 rounded-xl bg-white p-5 shadow-sm sm:p-7"><h2 class="font-display text-xl">Home page text</h2>
            <p class="text-xs text-ink/60">Tip: wrap a word in asterisks to show it in italic accent colour in headings, e.g. <code>Discover the *Authentic* Netherlands</code>.</p>
            <div><label class="field-label" for="c_hero_sub">Hero sub-heading</label><textarea id="c_hero_sub" name="c[hero_sub]" rows="2" class="field">{{ $c('hero_sub') }}</textarea></div>
            <fieldset class="grid gap-3 sm:grid-cols-3"><legend class="field-label">Three trust badges</legend>@foreach (range(0, 2) as $i)<input name="c[badges][{{ $i }}]" value="{{ $c('badges.'.$i) }}" class="field" aria-label="Badge {{ $i + 1 }}">@endforeach</fieldset>
            @foreach ([['why_heading', 'Why choose us heading'], ['tours_heading', 'Tour types heading'], ['packages_heading', 'Featured packages heading'], ['how_heading', 'How it works heading'], ['testimonials_heading', 'Testimonials heading'], ['faq_heading', 'FAQ heading'], ['cta_heading', 'Final call to action heading']] as [$k, $label])
                <div><label class="field-label" for="c_{{ $k }}">{{ $label }}</label><input id="c_{{ $k }}" name="c[{{ $k }}]" value="{{ $c($k) }}" class="field"></div>
            @endforeach
            <div><label class="field-label" for="c_cta_text">Final call to action text</label><input id="c_cta_text" name="c[cta_text]" value="{{ $c('cta_text') }}" class="field"></div>
            <fieldset class="space-y-3"><legend class="field-label">Why travellers choose us (4 items)</legend>@foreach (range(0, 3) as $i)<div class="grid gap-2 sm:grid-cols-[1fr_2fr]"><input name="c[why][{{ $i }}][title]" value="{{ $c('why.'.$i.'.title') }}" class="field" aria-label="Title {{ $i + 1 }}"><input name="c[why][{{ $i }}][text]" value="{{ $c('why.'.$i.'.text') }}" class="field" aria-label="Text {{ $i + 1 }}"></div>@endforeach</fieldset>
            <fieldset class="space-y-3"><legend class="field-label">How it works (3 steps)</legend>@foreach (range(0, 2) as $i)<div class="grid gap-2 sm:grid-cols-[1fr_2fr]"><input name="c[steps][{{ $i }}][title]" value="{{ $c('steps.'.$i.'.title') }}" class="field" aria-label="Step title {{ $i + 1 }}"><input name="c[steps][{{ $i }}][text]" value="{{ $c('steps.'.$i.'.text') }}" class="field" aria-label="Step text {{ $i + 1 }}"></div>@endforeach</fieldset>
        </section>

        {{-- Optional photo gallery section, shown at the end of the home page content. Fields appear when the box is ticked. --}}
        @php
            $gal = $page->gallery();
            $galItems = collect(old('gallery.items', $gal['items']))->values()->map(fn ($it, $i) => [
                'uid' => $i + 1, 'caption' => $it['caption'] ?? '', 'alt' => $it['alt'] ?? '', 'image' => $it['image'] ?? '',
            ])->all();
        @endphp
        <section class="space-y-4 rounded-xl bg-white p-5 shadow-sm sm:p-7" x-data="{ on: @js((bool) old('c.show_gallery', data_get($page->content, 'show_gallery', true))), items: @js($galItems), next: {{ count($galItems) + 1 }}, add() { this.items.push({ uid: this.next++, caption: '', alt: '', image: '' }) }, remove(i) { this.items.splice(i, 1) } }">
            <h2 class="font-display text-xl">Photo gallery section</h2>
            <input type="hidden" name="c[show_gallery]" value="0">
            <label class="flex items-start gap-2 text-sm font-semibold"><input type="checkbox" name="c[show_gallery]" value="1" x-model="on" class="mt-1"> Add the photo gallery to the home page (shown at the end of the home page content, after the testimonials)</label>
            <p class="text-xs text-ink/60" x-show="!on">Unticked: the gallery is not shown on the home page. Tick it to edit the section.</p>

            <div x-show="on" x-cloak class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="field-label" for="g_eyebrow">Small label above the heading</label><input id="g_eyebrow" name="gallery[eyebrow]" value="{{ old('gallery.eyebrow', $gal['eyebrow']) }}" class="field" maxlength="80"></div>
                    <div><label class="field-label" for="g_title">Heading</label><input id="g_title" name="gallery[title]" value="{{ old('gallery.title', $gal['title']) }}" class="field" maxlength="150"></div>
                </div>
                <p class="text-xs text-ink/60">Photos are laid out in a repeating pattern of 6 (one wide, two small, then two small and one wide), so use 6, 12 or 18 photos for a gap-free grid. Click a photo to enlarge it on the site.</p>

                <template x-for="(it, i) in items" :key="it.uid">
                    <div class="grid gap-3 rounded-lg border border-sand-200 p-3 sm:grid-cols-[6rem_1fr]">
                        <div>
                            <template x-if="it.image"><img :src="/^https?:/.test(it.image) ? it.image : '{{ rtrim(asset('/'), '/') }}/' + it.image" alt="" class="h-20 w-24 rounded-lg object-cover"></template>
                            <p x-show="!it.image" class="flex h-20 w-24 items-center justify-center rounded-lg bg-sand text-xs text-ink/60">No photo</p>
                        </div>
                        <div class="space-y-2">
                            <p class="label-caps text-xs text-rust" x-text="'Photo ' + (i + 1)"></p>
                            <input type="hidden" :name="`gallery[items][${i}][image]`" :value="it.image">
                            <div class="grid gap-2 sm:grid-cols-2">
                                <input type="text" :name="`gallery[items][${i}][caption]`" x-model="it.caption" class="field" placeholder="Caption (e.g. Couples)" aria-label="Caption" maxlength="80">
                                <input type="text" :name="`gallery[items][${i}][alt]`" x-model="it.alt" class="field" placeholder="Describe the photo (alt text)" aria-label="Alt text" maxlength="190">
                            </div>
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <input type="file" :name="`gallery[items][${i}][image_file]`" accept="image/jpeg,image/png,image/webp" class="text-xs" :aria-label="it.image ? 'Replace photo' : 'Upload photo'">
                                <button type="button" class="text-sm font-semibold text-rust underline" @click="remove(i)">Remove photo</button>
                            </div>
                        </div>
                    </div>
                </template>
                <button type="button" class="btn btn-outline !py-2 text-ink" @click="add()">+ Add photo</button>
            </div>
        </section>
    @else
        <section class="rounded-xl bg-white p-5 shadow-sm sm:p-7"><h2 class="font-display text-xl">Page content</h2>
            @if (in_array($page->slug, ['terms', 'privacy']))<p class="mb-2 text-sm text-ink/70">Type <code>[Company Name]</code> where the legal company name should appear. These are starter templates and need legal review.</p>@endif
            <input id="body" type="hidden" name="body" value="{{ old('body', $page->body) }}"><trix-editor input="body"></trix-editor></section>
    @endif
    <x-button type="submit" variant="secondary">Save page</x-button>
</form>
@endsection
