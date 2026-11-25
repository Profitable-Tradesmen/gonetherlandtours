@extends('admin.layout')
@section('title', 'Edit '.$page->title)
@section('content')
<div class="flex flex-wrap items-center justify-between gap-3">
    <h1 class="font-display text-3xl">Edit: {{ $page->title }}</h1>
    <a href="{{ $page->url() }}" target="_blank" class="text-sm font-semibold text-brand-700 underline">{{ $page->is_published || $page->slug === 'home' ? 'View page' : 'Preview draft' }} &nearr;</a>
</div>

<div class="mt-6 max-w-4xl space-y-6">
    {{-- Publish status: big and obvious, with its own button. Built-in pages are always live. --}}
    @unless ($page->is_system)
        @if ($page->is_published)
            <section class="flex flex-wrap items-center justify-between gap-4 rounded-xl border-2 border-emerald-500 bg-emerald-50 p-5" aria-label="Publish status">
                <div>
                    <p class="flex items-center gap-2 text-lg font-bold text-emerald-900"><span class="inline-block h-3 w-3 rounded-full bg-emerald-600"></span> This page is LIVE</p>
                    <p class="mt-1 text-sm text-emerald-900/80">Visitors can see it at <a href="{{ $page->url() }}" target="_blank" class="font-semibold underline">{{ $page->url() }}</a></p>
                </div>
                <form method="POST" action="{{ route('admin.pages.publish', $page) }}" onsubmit="return confirm('Unpublish this page? Visitors will see a not-found page until you publish it again.')">@csrf
                    <button class="rounded-full border-2 border-emerald-700 bg-white px-6 py-3 text-sm font-bold uppercase tracking-wide text-emerald-900 hover:bg-emerald-100">Unpublish (back to draft)</button>
                </form>
            </section>
        @else
            <section class="flex flex-wrap items-center justify-between gap-4 rounded-xl border-2 border-amber-500 bg-amber-50 p-5" aria-label="Publish status">
                <div>
                    <p class="flex items-center gap-2 text-lg font-bold text-amber-900"><span class="inline-block h-3 w-3 rounded-full bg-amber-500"></span> This page is a DRAFT</p>
                    <p class="mt-1 text-sm text-amber-900/80">Visitors cannot see it yet. Finish your sections, then publish. Only admins can preview it (<a href="{{ $page->url() }}" target="_blank" class="font-semibold underline">Preview draft</a>).</p>
                </div>
                <form method="POST" action="{{ route('admin.pages.publish', $page) }}">@csrf
                    <button class="rounded-full bg-emerald-600 px-8 py-3.5 text-base font-bold uppercase tracking-wide text-white shadow hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-300">Publish this page</button>
                </form>
            </section>
        @endif
    @endunless

    {{-- Sections: add, reorder, hide, edit, delete. Each section has its own edit form. --}}
    <section class="rounded-xl bg-white p-5 shadow-sm sm:p-7">
        <h2 class="font-display text-xl">Sections</h2>
        <p class="mt-1 text-sm text-ink/70">The page is shown top to bottom in this order. Use the arrows to reorder, <b>Hide</b> to take a section off the page without deleting it, and <b>Edit</b> to change its text, photos and buttons.</p>
        <ol class="mt-4 space-y-2">
            @php $all = $page->allSections(); @endphp
            @forelse ($all as $i => $s)
                @php $label = $types[$s['type']]['label']; $title = collect(['heading', 'left_heading', 'eyebrow'])->map(fn ($k) => $s['data'][$k] ?? null)->first(fn ($v) => filled($v)); @endphp
                <li id="s-{{ $s['id'] }}" class="flex flex-wrap items-center gap-3 rounded-lg border border-sand-200 p-3 {{ ($s['visible'] ?? true) ? '' : 'bg-sand/60 opacity-75' }}">
                    <span class="w-6 text-center text-xs font-bold text-ink/50">{{ $i + 1 }}</span>
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold">{{ $label }} @unless ($s['visible'] ?? true)<span class="ml-1 rounded bg-ink/10 px-1.5 py-0.5 text-[0.65rem] font-bold uppercase">Hidden</span>@endunless</p>
                        @if ($title)<p class="truncate text-xs text-ink/60">{{ \Illuminate\Support\Str::limit(strip_tags((string) $title), 90) }}</p>@endif
                    </div>
                    <div class="flex items-center gap-1 text-sm">
                        <form method="POST" action="{{ route('admin.pages.sections.move', [$page, $s['id'], 'up']) }}">@csrf<button class="px-2" title="Move up" aria-label="Move section up" @disabled($i === 0)>&uarr;</button></form>
                        <form method="POST" action="{{ route('admin.pages.sections.move', [$page, $s['id'], 'down']) }}">@csrf<button class="px-2" title="Move down" aria-label="Move section down" @disabled($i === count($all) - 1)>&darr;</button></form>
                        <form method="POST" action="{{ route('admin.pages.sections.toggle', [$page, $s['id']]) }}">@csrf<button class="px-2 underline">{{ ($s['visible'] ?? true) ? 'Hide' : 'Show' }}</button></form>
                        <a href="{{ route('admin.pages.sections.edit', [$page, $s['id']]) }}" class="px-2 font-semibold text-brand-700 underline">Edit</a>
                        <form method="POST" action="{{ route('admin.pages.sections.destroy', [$page, $s['id']]) }}" onsubmit="return confirm('Delete this section? This cannot be undone.')">@csrf @method('DELETE')<button class="px-2 text-rust underline">Delete</button></form>
                    </div>
                </li>
            @empty
                <li class="rounded-lg border border-dashed border-sand-200 p-4 text-sm text-ink/60">This page has no sections yet. Add one below.</li>
            @endforelse
        </ol>

        <form method="POST" action="{{ route('admin.pages.sections.add', $page) }}" class="mt-5 grid gap-3 rounded-lg bg-sand/60 p-4 sm:grid-cols-[1fr_auto]" x-data="{ t: '' }">
            @csrf
            <div>
                <label class="field-label" for="new-section">Add a section</label>
                <select id="new-section" name="type" class="field" x-model="t" required>
                    <option value="">Choose a section type...</option>
                    @foreach ($types as $key => $def)<option value="{{ $key }}">{{ $def['label'] }}</option>@endforeach
                </select>
                @foreach ($types as $key => $def)<p x-show="t === '{{ $key }}'" x-cloak class="mt-1 text-xs text-ink/70">{{ $def['description'] }}</p>@endforeach
            </div>
            <div class="flex items-end"><x-button type="submit" variant="secondary" class="!py-2.5">Add section</x-button></div>
        </form>
    </section>

    {{-- Page basics and SEO --}}
    <form method="POST" action="{{ route('admin.pages.update', $page) }}" class="space-y-4 rounded-xl bg-white p-5 shadow-sm sm:p-7">
        @csrf @method('PUT')
        <h2 class="font-display text-xl">Page basics and SEO</h2>
        <div class="grid gap-4 sm:grid-cols-2">
            <div><label class="field-label" for="title">Page name *</label><input id="title" name="title" value="{{ old('title', $page->title) }}" class="field" required></div>
            <div><label class="field-label" for="h1">Main heading (H1)</label><input id="h1" name="h1" value="{{ old('h1', $page->h1) }}" class="field"><p class="mt-1 text-xs text-ink/60">Used by sections that show the page heading.</p></div>
        </div>
        @unless ($page->is_system)
            <div><label class="field-label" for="slug">Web address</label><div class="flex max-w-xl items-center gap-1"><span class="text-sm text-ink/60">{{ url('/') }}/</span><input id="slug" name="slug" value="{{ old('slug', $page->slug) }}" class="field" required></div></div>
        @endunless
        <div><label class="field-label" for="seo_title">SEO title</label><input id="seo_title" name="seo_title" value="{{ old('seo_title', $page->seo_title) }}" class="field" maxlength="190"></div>
        <div><label class="field-label" for="meta_description">Meta description (about 155 characters)</label><textarea id="meta_description" name="meta_description" rows="2" class="field" maxlength="320">{{ old('meta_description', $page->meta_description) }}</textarea></div>
        <label class="flex items-center gap-2 text-sm font-semibold"><input type="checkbox" name="noindex" value="1" @checked(old('noindex', $page->noindex))> Hide this page from search engines (noindex)</label>
        @if (in_array($page->slug, ['terms', 'privacy']))
            <div><label class="field-label" for="version">Version (stored with each inquiry as the terms version)</label><input id="version" name="version" value="{{ old('version', $page->version) }}" class="field max-w-xs"><p class="mt-1 text-xs text-ink/60">Increase this whenever you change the legal text.</p></div>
        @endif
        <x-button type="submit" variant="secondary">Save page details</x-button>
    </form>

    @unless ($page->is_system)
        <form method="POST" action="{{ route('admin.pages.destroy', $page) }}" onsubmit="return confirm('Delete this page and all its sections? This cannot be undone.')" class="rounded-xl bg-white p-5 shadow-sm sm:p-7">
            @csrf @method('DELETE')
            <h2 class="font-display text-xl text-rust">Delete this page</h2>
            <p class="mt-1 text-sm text-ink/70">Removes the page and its sections permanently. Anything linking to it (menus, buttons) will lead to a "not found" page, so update those first.</p>
            <button class="mt-3 rounded-full border-2 border-rust px-5 py-2 text-sm font-bold text-rust hover:bg-rust hover:text-white">Delete page</button>
        </form>
    @endunless
</div>
@endsection
