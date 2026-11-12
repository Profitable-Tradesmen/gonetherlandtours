@extends('admin.layout')
@section('title', $cfg['plural'])
@section('content')
<div class="flex flex-wrap items-center justify-between gap-3">
    <h1 class="font-display text-3xl">{{ $cfg['plural'] }}</h1>
    <x-button :href="route($cfg['route'].'.create')" variant="secondary" class="!py-2">Add {{ strtolower($cfg['title']) }}</x-button>
</div>
@if (! empty($cfg['help']))<p class="mt-2 text-sm text-ink/70">{{ $cfg['help'] }}</p>@endif
<div class="mt-5 overflow-x-auto rounded-xl bg-white shadow-sm">
    <table class="w-full text-left text-sm">
        <thead class="bg-sand text-xs uppercase"><tr>@foreach ($cfg['columns'] as $label => $attr)<th class="p-3">{{ $label }}</th>@endforeach<th class="p-3 text-right">Actions</th></tr></thead>
        <tbody>
        @forelse ($items as $item)
            <tr class="border-t border-sand-200 align-top">
                @foreach ($cfg['columns'] as $label => $attr)<td class="p-3">{{ \Illuminate\Support\Str::limit((string) $item->{$attr}, 80) }}@if ($loop->first && isset($item->is_active) && ! $item->is_active) <span class="ml-1 rounded bg-sand px-1.5 py-0.5 text-xs">inactive</span>@endif</td>@endforeach
                <td class="whitespace-nowrap p-3 text-right">
                    @if ($cfg['sortable'] ?? true)
                        <form class="inline" method="POST" action="{{ route($cfg['route'].'.move', [$item->id, 'up']) }}">@csrf<button class="px-1" title="Move up" aria-label="Move up">&uarr;</button></form>
                        <form class="inline" method="POST" action="{{ route($cfg['route'].'.move', [$item->id, 'down']) }}">@csrf<button class="px-1" title="Move down" aria-label="Move down">&darr;</button></form>
                    @endif
                    <a class="px-2 font-semibold text-brand-700 underline" href="{{ route($cfg['route'].'.edit', $item->id) }}">Edit</a>
                    <form class="inline" method="POST" action="{{ route($cfg['route'].'.destroy', $item->id) }}" onsubmit="return confirm('Delete this item?')">@csrf @method('DELETE')<button class="px-2 text-rust underline">Delete</button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="{{ count($cfg['columns']) + 1 }}" class="p-4 text-ink/60">Nothing here yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
