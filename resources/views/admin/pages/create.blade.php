@extends('admin.layout')
@section('title', 'Add page')
@section('content')
<h1 class="font-display text-3xl">Add page</h1>
<form method="POST" action="{{ route('admin.pages.store') }}" class="mt-6 max-w-2xl space-y-5 rounded-xl bg-white p-5 shadow-sm sm:p-7">
    @csrf
    <div><label class="field-label" for="title">Page name *</label><input id="title" name="title" value="{{ old('title') }}" class="field" required maxlength="190" autofocus></div>
    <div><label class="field-label" for="slug">Web address (optional)</label><input id="slug" name="slug" value="{{ old('slug') }}" class="field" maxlength="120" placeholder="my-page-name"><p class="mt-1 text-xs text-ink/60">Lower-case letters, numbers and hyphens. Leave blank to build it from the name. The page will be at {{ url('/') }}/my-page-name.</p></div>
    <p class="text-sm text-ink/70">The page starts as a draft with a title bar and a text section. You choose what to add next and when to publish.</p>
    <div class="flex gap-3"><x-button type="submit" variant="secondary">Create page</x-button><x-button :href="route('admin.pages.index')" variant="outline" class="text-ink">Cancel</x-button></div>
</form>
@endsection
