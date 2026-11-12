@extends('admin.layout')
@section('title', 'Enquiry #'.$e->id)
@php $row = fn ($label, $value) => $value !== null && $value !== '' ? '<div class="flex gap-3 py-1.5 text-sm"><dt class="w-40 shrink-0 font-semibold">'.e($label).'</dt><dd class="min-w-0 break-words">'.e($value).'</dd></div>' : ''; @endphp
@section('content')
<a href="{{ route('admin.enquiries.index') }}" class="text-sm underline">&larr; All enquiries</a>
<h1 class="mt-2 font-display text-3xl">{{ $e->fullName() }} <span class="text-lg text-ink/50">#{{ $e->id }}</span></h1>
<div class="mt-6 grid gap-6 lg:grid-cols-[1.5fr_1fr]">
    <div class="space-y-6">
        <section class="rounded-xl bg-white p-5 shadow-sm"><h2 class="font-display text-xl">Details</h2><dl class="mt-2 divide-y divide-sand-200">
            {!! $row('Received', $e->created_at->format('d M Y H:i')) !!}{!! $row('Form', config('gnt.form_names.'.$e->form_name, $e->form_name)) !!}
            {!! $row('Email', $e->email) !!}{!! $row('Phone (E.164)', $e->phone_e164) !!}{!! $row('Package', $e->package_name) !!}
            {!! $row('Travel date', $e->travel_date) !!}{!! $row('Second quote', $e->second_quote) !!}{!! $row('Special request', $e->special_request) !!}
        </dl></section>
        @if ($e->quiz_answers)<section class="rounded-xl bg-white p-5 shadow-sm"><h2 class="font-display text-xl">Quiz answers</h2><dl class="mt-2 divide-y divide-sand-200">@foreach ($e->quiz_answers as $k => $v){!! $row(ucfirst(str_replace('_', ' ', $k)), $v) !!}@endforeach</dl></section>@endif
        <section class="rounded-xl bg-white p-5 shadow-sm"><h2 class="font-display text-xl">Consent evidence</h2><dl class="mt-2 divide-y divide-sand-200">
            {!! $row('Consent given', $e->consent_given ? 'Yes' : 'No') !!}{!! $row('Consent timestamp', $e->consented_at?->toIso8601String()) !!}{!! $row('Terms version', $e->terms_version) !!}
            {!! $row('IP address', $e->ip_address) !!}{!! $row('User agent', $e->user_agent) !!}{!! $row('Page URL', $e->page_url) !!}
            <div class="py-2 text-sm"><dt class="font-semibold">Consent text shown</dt><dd class="mt-1 rounded bg-sand p-3 text-xs leading-relaxed">{{ $e->consent_text }}</dd></div>
        </dl></section>
        <section class="rounded-xl bg-white p-5 shadow-sm"><h2 class="font-display text-xl">Tracking</h2>
            @foreach (['First touch' => $e->tracking_first, 'Latest touch' => $e->tracking_last] as $label => $t)
                <h3 class="mt-3 text-sm font-semibold text-rust">{{ $label }}</h3><dl class="divide-y divide-sand-200">@foreach (($t ?? []) as $k => $v){!! $row($k, is_scalar($v) ? $v : json_encode($v)) !!}@endforeach</dl>
            @endforeach
        </section>
    </div>
    <div class="space-y-6">
        <form method="POST" action="{{ route('admin.enquiries.update', $e) }}" class="rounded-xl bg-white p-5 shadow-sm">@csrf @method('PUT')
            <h2 class="font-display text-xl">Status and notes</h2>
            <label class="field-label mt-3" for="status">Status</label>
            <select id="status" name="status" class="field">@foreach (\App\Models\Enquiry::STATUSES as $k => $l)<option value="{{ $k }}" @selected($e->status === $k)>{{ $l }}</option>@endforeach</select>
            <label class="field-label mt-3" for="notes">Internal notes</label><textarea id="notes" name="notes" rows="6" class="field">{{ old('notes', $e->notes) }}</textarea>
            <button class="btn btn-secondary btn-block mt-4">Save</button>
        </form>
        <section class="rounded-xl bg-white p-5 shadow-sm"><h2 class="font-display text-xl">GoHighLevel</h2>
            <dl class="mt-2">{!! $row('Sync status', $e->ghl_status) !!}{!! $row('Contact ID', $e->ghl_contact_id) !!}{!! $row('Last synced', $e->ghl_synced_at?->format('d M Y H:i')) !!}{!! $row('Last error', $e->ghl_error) !!}</dl>
            <form method="POST" action="{{ route('admin.enquiries.ghl-retry', $e) }}" class="mt-3">@csrf<button class="btn btn-primary btn-block !py-2">Retry sync</button></form>
        </section>
        <form method="POST" action="{{ route('admin.enquiries.destroy', $e) }}" onsubmit="return confirm('Delete this enquiry permanently?')">@csrf @method('DELETE')<button class="text-sm text-rust underline">Delete enquiry</button></form>
    </div>
</div>
@endsection
