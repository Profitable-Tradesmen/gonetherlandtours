@extends('admin.layout')
@section('title', 'Header & Footer')
@section('content')
<div class="flex flex-wrap items-center justify-between gap-3">
    <h1 class="font-display text-3xl">Header &amp; Footer</h1>
    <x-button :href="route('admin.snippets.create')" variant="secondary" class="!py-2">Add snippet</x-button>
</div>
<div class="mt-3 max-w-3xl rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900" role="note">
    <p class="font-semibold">Careful: this code is added to your live site exactly as you type it.</p>
    <p class="mt-1">Invalid or broken code (an unclosed tag, a script error, a wrong tracking snippet) can break the layout or stop pages from working for every visitor. Only paste code from a source you trust, test on one page first, and switch a snippet off to remove it quickly. Only administrators can see this page.</p>
</div>
<p class="mt-3 max-w-3xl text-sm text-ink/70">Snippets load in the order below (lowest order number first). Use it for analytics scripts, tracking pixels, chat widgets, custom CSS or any HTML. The site's own scripts and styles are not affected.</p>

<div class="mt-5 overflow-x-auto rounded-xl bg-white shadow-sm"><table class="w-full text-left text-sm">
    <thead class="bg-sand text-xs uppercase"><tr><th class="p-3">Order</th><th class="p-3">Name</th><th class="p-3">Scope</th><th class="p-3">Placement</th><th class="p-3">Status</th><th class="p-3 text-right">Actions</th></tr></thead>
    <tbody>
    @forelse ($snippets as $s)
        @php
            $inc = \App\Models\CodeSnippet::parsePaths($s->include_paths);
            $exc = \App\Models\CodeSnippet::parsePaths($s->exclude_paths);
        @endphp
        <tr class="border-t border-sand-200 align-top">
            <td class="p-3">{{ $s->sort_order }}</td>
            <td class="p-3 font-semibold">{{ $s->name }}</td>
            <td class="p-3">
                @if ($s->scope === 'only')Only {{ count($inc) }} {{ \Illuminate\Support\Str::plural('page', count($inc)) }}<span class="block text-xs text-ink/60">{{ \Illuminate\Support\Str::limit(implode(', ', $inc), 60) }}</span>@else Entire website @endif
                @if ($exc)<span class="block text-xs text-ink/60">Except {{ \Illuminate\Support\Str::limit(implode(', ', $exc), 50) }}</span>@endif
            </td>
            <td class="p-3">{{ ['head' => 'Head', 'body_start' => 'Body start', 'body_end' => 'Body end'][$s->position] ?? $s->position }}</td>
            <td class="p-3"><form method="POST" action="{{ route('admin.snippets.toggle', $s->id) }}">@csrf<button class="rounded px-2 py-0.5 text-xs {{ $s->is_active ? 'bg-emerald-100 text-emerald-900' : 'bg-sand' }}" aria-label="{{ $s->is_active ? 'Disable' : 'Enable' }} {{ $s->name }}">{{ $s->is_active ? 'Enabled' : 'Disabled' }}</button></form></td>
            <td class="whitespace-nowrap p-3 text-right">
                <a class="px-2 font-semibold text-brand-700 underline" href="{{ route('admin.snippets.edit', $s->id) }}">Edit</a>
                <form class="inline" method="POST" action="{{ route('admin.snippets.destroy', $s->id) }}" onsubmit="return confirm('Delete this snippet? It will stop loading on the site.')">@csrf @method('DELETE')<button class="px-2 text-rust underline">Delete</button></form>
            </td>
        </tr>
    @empty
        <tr><td colspan="6" class="p-6 text-center text-ink/70">No snippets yet. Click "Add snippet" to add your first script or style.</td></tr>
    @endforelse
    </tbody></table></div>
@endsection
