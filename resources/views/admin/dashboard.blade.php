@extends('admin.layout')
@section('title', 'Dashboard')
@section('content')
<h1 class="font-display text-3xl">Dashboard</h1>
<div class="mt-6 grid gap-4 sm:grid-cols-3">
    <div class="rounded-xl bg-white p-5 shadow-sm"><p class="text-sm text-ink/60">Enquiries this week</p><p class="font-display text-4xl">{{ $weekCount }}</p></div>
    <a href="{{ route('admin.enquiries.index', ['status' => 'new']) }}" class="rounded-xl bg-white p-5 shadow-sm hover:ring-2 hover:ring-accent"><p class="text-sm text-ink/60">New (unactioned)</p><p class="font-display text-4xl">{{ $newCount }}</p></a>
    <div class="rounded-xl bg-white p-5 shadow-sm"><p class="text-sm text-ink/60">GoHighLevel sync failures</p><p class="font-display text-4xl {{ $ghlFailed ? 'text-rust' : '' }}">{{ $ghlFailed }}</p><p class="text-xs text-ink/60">{{ ghl_enabled() ? 'GHL is ON' : 'GHL is OFF (no token set)' }}</p></div>
</div>
<div class="mt-8 flex flex-wrap gap-2 text-sm">
    <x-button :href="route('admin.packages.create')" variant="secondary" class="!py-2">New package</x-button>
    <x-button :href="route('admin.pages.edit', 1)" variant="secondary" class="!py-2">Edit home page</x-button>
    <x-button :href="route('admin.quiz.index')" variant="secondary" class="!py-2">Edit quiz</x-button>
    <x-button :href="route('admin.settings')" variant="secondary" class="!py-2">Site settings</x-button>
</div>
<h2 class="mt-10 font-display text-2xl">Recent enquiries</h2>
<div class="mt-3 overflow-x-auto rounded-xl bg-white shadow-sm">
    <table class="w-full text-left text-sm">
        <thead class="bg-sand text-xs uppercase"><tr><th class="p-3">When</th><th class="p-3">Name</th><th class="p-3">Form</th><th class="p-3">Status</th></tr></thead>
        <tbody>@forelse ($recent as $e)
            <tr class="border-t border-sand-200"><td class="p-3">{{ $e->created_at->diffForHumans() }}</td><td class="p-3"><a class="font-semibold text-brand-700 underline" href="{{ route('admin.enquiries.show', $e) }}">{{ $e->fullName() }}</a></td><td class="p-3">{{ config('gnt.form_names.'.$e->form_name, $e->form_name) }}</td><td class="p-3">{{ \App\Models\Enquiry::STATUSES[$e->status] ?? $e->status }}</td></tr>
        @empty<tr><td colspan="4" class="p-4 text-ink/60">No enquiries yet.</td></tr>@endforelse</tbody>
    </table>
</div>
@endsection
