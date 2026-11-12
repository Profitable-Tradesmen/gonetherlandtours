@extends('admin.layout')
@section('title', 'Quiz')
@section('content')
<div class="flex flex-wrap items-center justify-between gap-3"><h1 class="font-display text-3xl">Hero quiz</h1><x-button :href="route('admin.quiz.create')" variant="secondary" class="!py-2">Add step</x-button></div>
<p class="mt-2 text-sm text-ink/70">Each step is one question. The final contact-details step is always added automatically. Quiz answers are saved with each enquiry and sent to GoHighLevel, so avoid changing a step's key once leads exist.</p>
<div class="mt-5 overflow-x-auto rounded-xl bg-white shadow-sm"><table class="w-full text-left text-sm">
    <thead class="bg-sand text-xs uppercase"><tr><th class="p-3">#</th><th class="p-3">Question</th><th class="p-3">Key</th><th class="p-3">Type</th><th class="p-3">Options</th><th class="p-3">Active</th><th class="p-3 text-right">Actions</th></tr></thead>
    <tbody>@foreach ($steps as $s)
        <tr class="border-t border-sand-200 align-top"><td class="p-3">{{ $loop->iteration }}</td><td class="p-3">{{ $s->question }}</td><td class="p-3"><code>{{ $s->key }}</code></td><td class="p-3">{{ $s->type }}</td><td class="p-3">{{ $s->options_count }}</td>
        <td class="p-3"><form method="POST" action="{{ route('admin.quiz.toggle', $s) }}">@csrf<button class="rounded px-2 py-0.5 text-xs {{ $s->is_active ? 'bg-emerald-100 text-emerald-900' : 'bg-sand' }}">{{ $s->is_active ? 'Active' : 'Off' }}</button></form></td>
        <td class="whitespace-nowrap p-3 text-right">
            <form class="inline" method="POST" action="{{ route('admin.quiz.move', [$s, 'up']) }}">@csrf<button class="px-1" aria-label="Move up">&uarr;</button></form>
            <form class="inline" method="POST" action="{{ route('admin.quiz.move', [$s, 'down']) }}">@csrf<button class="px-1" aria-label="Move down">&darr;</button></form>
            <a class="px-2 font-semibold text-brand-700 underline" href="{{ route('admin.quiz.edit', $s) }}">Edit</a>
            <form class="inline" method="POST" action="{{ route('admin.quiz.destroy', $s) }}" onsubmit="return confirm('Delete this step?')">@csrf @method('DELETE')<button class="px-2 text-rust underline">Delete</button></form></td></tr>
    @endforeach</tbody></table></div>
@endsection
