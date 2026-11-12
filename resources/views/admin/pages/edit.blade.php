@extends('admin.layout')
@section('title', 'Edit '.$page->title)
@php $c = fn ($k, $d = '') => old('c.'.$k, data_get($page->content, $k, $d)); @endphp
@section('content')
<h1 class="font-display text-3xl">Edit: {{ $page->title }}</h1>
<form method="POST" action="{{ route('admin.pages.update', $page) }}" class="mt-6 max-w-4xl space-y-6">
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
            <div><label class="field-label" for="c_hero_sub">Hero sub-heading</label><textarea id="c_hero_sub" name="c[hero_sub]" rows="2" class="field">{{ $c('hero_sub') }}</textarea></div>
            <fieldset class="grid gap-3 sm:grid-cols-3"><legend class="field-label">Three trust badges</legend>@foreach (range(0, 2) as $i)<input name="c[badges][{{ $i }}]" value="{{ $c('badges.'.$i) }}" class="field" aria-label="Badge {{ $i + 1 }}">@endforeach</fieldset>
            @foreach ([['why_heading', 'Why choose us heading'], ['tours_heading', 'Tour types heading'], ['packages_heading', 'Featured packages heading'], ['how_heading', 'How it works heading'], ['testimonials_heading', 'Testimonials heading'], ['faq_heading', 'FAQ heading'], ['cta_heading', 'Final call to action heading']] as [$k, $label])
                <div><label class="field-label" for="c_{{ $k }}">{{ $label }}</label><input id="c_{{ $k }}" name="c[{{ $k }}]" value="{{ $c($k) }}" class="field"></div>
            @endforeach
            <div><label class="field-label" for="c_cta_text">Final call to action text</label><input id="c_cta_text" name="c[cta_text]" value="{{ $c('cta_text') }}" class="field"></div>
            <fieldset class="space-y-3"><legend class="field-label">Why travellers choose us (4 items)</legend>@foreach (range(0, 3) as $i)<div class="grid gap-2 sm:grid-cols-[1fr_2fr]"><input name="c[why][{{ $i }}][title]" value="{{ $c('why.'.$i.'.title') }}" class="field" aria-label="Title {{ $i + 1 }}"><input name="c[why][{{ $i }}][text]" value="{{ $c('why.'.$i.'.text') }}" class="field" aria-label="Text {{ $i + 1 }}"></div>@endforeach</fieldset>
            <fieldset class="space-y-3"><legend class="field-label">How it works (3 steps)</legend>@foreach (range(0, 2) as $i)<div class="grid gap-2 sm:grid-cols-[1fr_2fr]"><input name="c[steps][{{ $i }}][title]" value="{{ $c('steps.'.$i.'.title') }}" class="field" aria-label="Step title {{ $i + 1 }}"><input name="c[steps][{{ $i }}][text]" value="{{ $c('steps.'.$i.'.text') }}" class="field" aria-label="Step text {{ $i + 1 }}"></div>@endforeach</fieldset>
        </section>
    @else
        <section class="rounded-xl bg-white p-5 shadow-sm sm:p-7"><h2 class="font-display text-xl">Page content</h2>
            @if (in_array($page->slug, ['terms', 'privacy']))<p class="mb-2 text-sm text-ink/70">Type <code>[Company Name]</code> where the legal company name should appear. These are starter templates and need legal review.</p>@endif
            <input id="body" type="hidden" name="body" value="{{ old('body', $page->body) }}"><trix-editor input="body"></trix-editor></section>
    @endif
    <x-button type="submit" variant="secondary">Save page</x-button>
</form>
@endsection
