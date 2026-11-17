@extends('admin.layout')
@section('title', 'Pages')
@section('content')
<div class="flex flex-wrap items-center justify-between gap-3">
    <h1 class="font-display text-3xl">Pages</h1>
    <x-button :href="route('admin.pages.create')" variant="secondary" class="!py-2">Add page</x-button>
</div>
<p class="mt-2 max-w-3xl text-sm text-ink/70">Every page is built from sections you can add, reorder, hide and edit. Built-in pages (Home, About, Contact, FAQ, Testimonials, Terms, Privacy) keep their web address; pages you add live at <code>/your-page-name</code>. Packages, destinations, tour types, FAQs, testimonials and blog posts have their own menu items.</p>
<div class="mt-5 overflow-x-auto rounded-xl bg-white shadow-sm"><table class="w-full text-left text-sm">
    <thead class="bg-sand text-xs uppercase"><tr><th class="p-3">Page</th><th class="p-3">Address</th><th class="p-3">Status</th><th class="p-3">Sections</th><th class="p-3">Updated</th><th class="p-3 text-right">Actions</th></tr></thead>
    <tbody>@foreach ($pages as $p)
        <tr class="border-t border-sand-200">
            <td class="p-3 font-semibold">{{ $p->title }} @if ($p->is_system)<span class="ml-1 rounded bg-sand px-1.5 py-0.5 text-[0.65rem] font-bold uppercase text-ink/60">Built-in</span>@endif</td>
            <td class="p-3"><a href="{{ $p->url() }}" target="_blank" class="text-brand-700 underline">{{ $p->path() }}</a></td>
            <td class="p-3">{{ ! $p->is_published && ! $p->is_system ? 'Draft (not public)' : ($p->noindex ? 'Hidden from search engines' : 'Live') }}</td>
            <td class="p-3">{{ count($p->allSections()) }}</td>
            <td class="p-3">{{ $p->updated_at->format('d M Y') }}</td>
            <td class="p-3 text-right"><a class="font-semibold text-brand-700 underline" href="{{ route('admin.pages.edit', $p) }}">Edit</a></td>
        </tr>@endforeach</tbody></table></div>
@endsection
