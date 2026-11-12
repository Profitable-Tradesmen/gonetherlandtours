@extends('admin.layout')
@section('title', 'Enquiries')
@section('content')
<div class="flex flex-wrap items-center justify-between gap-3">
    <h1 class="font-display text-3xl">Enquiries</h1>
    <x-button :href="route('admin.enquiries.export', request()->query())" variant="secondary" class="!py-2">Export CSV</x-button>
</div>
<form method="GET" class="mt-5 grid gap-3 rounded-xl bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-6" aria-label="Filter enquiries">
    <div class="lg:col-span-2"><label class="field-label" for="q">Search</label><input id="q" name="q" value="{{ request('q') }}" class="field" placeholder="Name, email, phone, package"></div>
    <div><label class="field-label" for="status">Status</label><select id="status" name="status" class="field"><option value="">All</option>@foreach (\App\Models\Enquiry::STATUSES as $k => $l)<option value="{{ $k }}" @selected(request('status') === $k)>{{ $l }}</option>@endforeach</select></div>
    <div><label class="field-label" for="source">Source (utm_source)</label><input id="source" name="source" value="{{ request('source') }}" class="field"></div>
    <div><label class="field-label" for="from">From</label><input id="from" type="date" name="from" value="{{ request('from') }}" class="field"></div>
    <div><label class="field-label" for="to">To</label><input id="to" type="date" name="to" value="{{ request('to') }}" class="field"></div>
    <div class="sm:col-span-2 lg:col-span-6"><button class="btn btn-primary !py-2">Filter</button> <a href="{{ route('admin.enquiries.index') }}" class="ml-2 text-sm underline">Reset</a></div>
</form>
<div class="mt-5 overflow-x-auto rounded-xl bg-white shadow-sm">
    <table class="w-full text-left text-sm">
        <thead class="bg-sand text-xs uppercase"><tr><th class="p-3">Received</th><th class="p-3">Name</th><th class="p-3">Contact</th><th class="p-3">Form / package</th><th class="p-3">Source</th><th class="p-3">Status</th><th class="p-3">GHL</th></tr></thead>
        <tbody>@forelse ($enquiries as $e)
            <tr class="border-t border-sand-200 align-top">
                <td class="whitespace-nowrap p-3">{{ $e->created_at->format('d M Y H:i') }}</td>
                <td class="p-3"><a class="font-semibold text-brand-700 underline" href="{{ route('admin.enquiries.show', $e) }}">{{ $e->fullName() }}</a></td>
                <td class="p-3">{{ $e->email }}<br><span class="text-ink/60">{{ $e->phone_e164 }}</span></td>
                <td class="p-3">{{ config('gnt.form_names.'.$e->form_name, $e->form_name) }}@if ($e->package_name)<br><span class="text-ink/60">{{ $e->package_name }}</span>@endif</td>
                <td class="p-3">{{ data_get($e->tracking_first, 'utm_source', '-') }}</td>
                <td class="p-3"><span class="rounded bg-sand px-2 py-0.5">{{ \App\Models\Enquiry::STATUSES[$e->status] ?? $e->status }}</span></td>
                <td class="p-3">{{ $e->ghl_status }}</td>
            </tr>
        @empty<tr><td colspan="7" class="p-4 text-ink/60">No enquiries match.</td></tr>@endforelse</tbody>
    </table>
</div>
<div class="mt-4">{{ $enquiries->links() }}</div>
@endsection
