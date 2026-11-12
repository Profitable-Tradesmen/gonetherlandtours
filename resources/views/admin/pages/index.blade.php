@extends('admin.layout')
@section('title', 'Pages')
@section('content')
<h1 class="font-display text-3xl">Pages</h1>
<p class="mt-2 text-sm text-ink/70">Edit the home page text, About, Contact and the legal pages. Packages, destinations, FAQs and testimonials have their own menu items.</p>
<div class="mt-5 overflow-x-auto rounded-xl bg-white shadow-sm"><table class="w-full text-left text-sm">
    <thead class="bg-sand text-xs uppercase"><tr><th class="p-3">Page</th><th class="p-3">Search engines</th><th class="p-3">Updated</th><th class="p-3 text-right">Actions</th></tr></thead>
    <tbody>@foreach ($pages as $p)<tr class="border-t border-sand-200"><td class="p-3 font-semibold">{{ $p->title }}</td><td class="p-3">{{ $p->noindex ? 'Hidden (noindex)' : 'Indexed' }}</td><td class="p-3">{{ $p->updated_at->format('d M Y') }}</td><td class="p-3 text-right"><a class="font-semibold text-brand-700 underline" href="{{ route('admin.pages.edit', $p) }}">Edit</a></td></tr>@endforeach</tbody></table></div>
@endsection
