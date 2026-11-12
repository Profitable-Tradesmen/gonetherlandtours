@extends('admin.layout')
@section('title', 'Packages')
@section('content')
<div class="flex flex-wrap items-center justify-between gap-3"><h1 class="font-display text-3xl">Packages</h1><x-button :href="route('admin.packages.create')" variant="secondary" class="!py-2">New package</x-button></div>
<div class="mt-5 overflow-x-auto rounded-xl bg-white shadow-sm">
    <table class="w-full text-left text-sm">
        <thead class="bg-sand text-xs uppercase"><tr><th class="p-3">Title</th><th class="p-3">Type</th><th class="p-3">Nights</th><th class="p-3">Published</th><th class="p-3">Featured</th><th class="p-3 text-right">Actions</th></tr></thead>
        <tbody>@forelse ($packages as $p)
            <tr class="border-t border-sand-200 align-top">
                <td class="p-3 font-semibold">{{ $p->title }}@if ($p->is_sample) <span class="ml-1 rounded bg-accent/30 px-1.5 py-0.5 text-xs">sample</span>@endif</td>
                <td class="p-3">{{ $p->tourType?->name }}</td><td class="p-3">{{ $p->nights }}</td>
                <td class="p-3"><form method="POST" action="{{ route('admin.packages.toggle', [$p, 'is_published']) }}">@csrf<button class="rounded px-2 py-0.5 text-xs {{ $p->is_published ? 'bg-emerald-100 text-emerald-900' : 'bg-sand' }}">{{ $p->is_published ? 'Published' : 'Draft' }}</button></form></td>
                <td class="p-3"><form method="POST" action="{{ route('admin.packages.toggle', [$p, 'is_featured']) }}">@csrf<button class="rounded px-2 py-0.5 text-xs {{ $p->is_featured ? 'bg-accent/40' : 'bg-sand' }}">{{ $p->is_featured ? 'Featured' : 'No' }}</button></form></td>
                <td class="whitespace-nowrap p-3 text-right">
                    <form class="inline" method="POST" action="{{ route('admin.packages.move', [$p, 'up']) }}">@csrf<button class="px-1" aria-label="Move up">&uarr;</button></form>
                    <form class="inline" method="POST" action="{{ route('admin.packages.move', [$p, 'down']) }}">@csrf<button class="px-1" aria-label="Move down">&darr;</button></form>
                    <a class="px-2 font-semibold text-brand-700 underline" href="{{ route('admin.packages.edit', $p) }}">Edit</a>
                    <form class="inline" method="POST" action="{{ route('admin.packages.duplicate', $p) }}">@csrf<button class="px-2 underline">Duplicate</button></form>
                    <form class="inline" method="POST" action="{{ route('admin.packages.destroy', $p) }}" onsubmit="return confirm('Delete this package?')">@csrf @method('DELETE')<button class="px-2 text-rust underline">Delete</button></form>
                </td>
            </tr>
        @empty<tr><td colspan="6" class="p-4 text-ink/60">No packages yet.</td></tr>@endforelse</tbody>
    </table>
</div>
@endsection
