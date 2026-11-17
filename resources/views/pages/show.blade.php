@extends('layouts.app')
{{-- Every page of the site: the ordered sections edited in Admin > Pages. Each section type is resources/views/sections/{type}.blade.php --}}
@section('content')
@if (! empty($draft))
    <div class="bg-amber-100 px-4 py-3 text-center text-sm font-semibold text-amber-900" role="status">Draft preview: only logged-in admins can see this page. To publish it, click "Publish this page" at the top of <a href="{{ route('admin.pages.edit', $page) }}" class="underline">its page editor</a>.</div>
@endif
@forelse ($sections as $section)
    @include('sections.'.$section['type'], ['d' => $section['d'], 'sid' => $section['id']])
@empty
    <section class="container-site py-20 text-center"><h1 class="text-4xl font-medium">{{ $page->h1 ?: $page->title }}</h1></section>
@endforelse
@endsection
