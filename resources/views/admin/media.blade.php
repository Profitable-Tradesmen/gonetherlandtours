@extends('admin.layout')
@section('title', 'Media')
@section('content')
<h1 class="font-display text-3xl">Media library</h1>
<form method="POST" enctype="multipart/form-data" action="{{ route('admin.media.store') }}" class="mt-5 grid gap-3 rounded-xl bg-white p-5 shadow-sm sm:grid-cols-[1fr_1fr_auto]">@csrf
    <div><label class="field-label" for="file">Image (JPG, PNG, WebP, max 8 MB)</label><input id="file" type="file" name="file" accept="image/jpeg,image/png,image/webp" class="field" required></div>
    <div><label class="field-label" for="alt">Alt text *</label><input id="alt" name="alt" class="field" required></div>
    <div class="flex items-end"><button class="btn btn-secondary">Upload</button></div>
</form>
<p class="mt-2 text-xs text-ink/60">Uploads are resized (480, 960 and 1600 px wide) and converted to WebP. Attach images to packages from the package edit screen.</p>
<div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    @foreach ($items as $m)
        <div class="rounded-xl bg-white p-3 shadow-sm"><img src="{{ $m->url(480) }}" alt="{{ $m->alt }}" width="480" height="320" class="aspect-[3/2] w-full rounded object-cover" loading="lazy">
            <form method="POST" action="{{ route('admin.media.update', $m) }}" class="mt-2">@csrf @method('PUT')<input name="alt" value="{{ $m->alt }}" class="field !py-1.5 text-sm" aria-label="Alt text"><button class="mt-1 text-xs underline">Save alt</button></form>
            <form method="POST" action="{{ route('admin.media.destroy', $m) }}" onsubmit="return confirm('Delete this image everywhere it is used?')">@csrf @method('DELETE')<button class="mt-1 text-xs text-rust underline">Delete</button></form></div>
    @endforeach
</div>
<div class="mt-4">{{ $items->links() }}</div>
@endsection
