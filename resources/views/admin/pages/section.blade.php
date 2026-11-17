@extends('admin.layout')
@section('title', 'Edit section')
@section('content')
<nav class="text-sm text-ink/60"><a href="{{ route('admin.pages.index') }}" class="underline">Pages</a> / <a href="{{ route('admin.pages.edit', $page) }}" class="underline">{{ $page->title }}</a> / <span>{{ $def['label'] }}</span></nav>
<h1 class="mt-2 font-display text-3xl">Edit section: {{ $def['label'] }}</h1>
<p class="mt-1 max-w-3xl text-sm text-ink/70">{{ $def['description'] }}</p>
<p class="mt-1 text-xs text-ink/60">Tip: in headings, wrap a word in asterisks to show it in an italic accent colour, e.g. <code>Discover the *real* Netherlands</code>.</p>

<form method="POST" enctype="multipart/form-data" action="{{ route('admin.pages.sections.update', [$page, $section['id']]) }}" class="mt-6 max-w-4xl space-y-5 rounded-xl bg-white p-5 shadow-sm sm:p-7">
    @csrf @method('PUT')
    @foreach ($def['fields'] as $f)
        @if ($f['type'] === 'repeater')
            @include('admin.pages.repeater', ['f' => $f, 'data' => $data])
        @else
            @include('admin.pages.field', ['f' => $f, 'data' => $data])
        @endif
    @endforeach
    <div class="flex flex-wrap gap-3 border-t border-sand-200 pt-5">
        <x-button type="submit" variant="secondary">Save section</x-button>
        <x-button :href="route('admin.pages.edit', $page)" variant="outline" class="text-ink">Back to the page</x-button>
    </div>
</form>
@endsection
