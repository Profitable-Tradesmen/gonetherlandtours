<!DOCTYPE html>
<html><body style="font-family:Arial,sans-serif;color:#0C0D11;line-height:1.5">
<h2 style="margin:0 0 12px">New enquiry #{{ $enquiry->id }}</h2>
<table cellpadding="6" style="border-collapse:collapse">
    <tr><td><strong>Name</strong></td><td>{{ $enquiry->fullName() }}</td></tr>
    <tr><td><strong>Email</strong></td><td>{{ $enquiry->email }}</td></tr>
    <tr><td><strong>Phone</strong></td><td>{{ $enquiry->phone_e164 }}</td></tr>
    <tr><td><strong>Form</strong></td><td>{{ config('gnt.form_names.'.$enquiry->form_name, $enquiry->form_name) }}</td></tr>
    @if ($enquiry->package_name)<tr><td><strong>Package</strong></td><td>{{ $enquiry->package_name }}</td></tr>@endif
    @if ($enquiry->travel_date)<tr><td><strong>Travel date</strong></td><td>{{ $enquiry->travel_date }}</td></tr>@endif
    @if ($enquiry->second_quote)<tr><td><strong>Second quote</strong></td><td>{{ $enquiry->second_quote }}</td></tr>@endif
    @if ($enquiry->special_request)<tr><td><strong>Special request</strong></td><td>{{ $enquiry->special_request }}</td></tr>@endif
    @foreach (($enquiry->quiz_answers ?? []) as $k => $v)<tr><td><strong>{{ ucfirst(str_replace('_', ' ', $k)) }}</strong></td><td>{{ $v }}</td></tr>@endforeach
    <tr><td><strong>Source</strong></td><td>{{ data_get($enquiry->tracking_first, 'utm_source', 'direct/unknown') }} / {{ data_get($enquiry->tracking_first, 'utm_campaign', '-') }}</td></tr>
</table>
<p><a href="{{ route('admin.enquiries.show', $enquiry) }}">Open in admin</a></p>
</body></html>
