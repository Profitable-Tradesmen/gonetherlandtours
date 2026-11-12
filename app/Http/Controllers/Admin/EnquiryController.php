<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SyncEnquiryToGhl;
use App\Models\Enquiry;
use Illuminate\Http\Request;

class EnquiryController extends Controller
{
    private function filtered(Request $request)
    {
        return Enquiry::query()
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->q.'%';
                $q->where(fn ($w) => $w->where('first_name', 'like', $term)->orWhere('last_name', 'like', $term)
                    ->orWhere('email', 'like', $term)->orWhere('phone_e164', 'like', $term)->orWhere('package_name', 'like', $term));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('source'), fn ($q) => $q->where('tracking_first->utm_source', $request->source))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->to))
            ->latest();
    }

    public function index(Request $request)
    {
        return view('admin.enquiries.index', ['enquiries' => $this->filtered($request)->paginate(25)->withQueryString()]);
    }

    public function show(Enquiry $enquiry)
    {
        return view('admin.enquiries.show', ['e' => $enquiry]);
    }

    public function update(Request $request, Enquiry $enquiry)
    {
        $data = $request->validate([
            'status' => 'required|in:'.implode(',', array_keys(Enquiry::STATUSES)),
            'notes' => 'nullable|string|max:5000',
        ]);
        $enquiry->update($data);

        return back()->with('status', 'Enquiry updated.');
    }

    public function destroy(Enquiry $enquiry)
    {
        $enquiry->delete();

        return redirect()->route('admin.enquiries.index')->with('status', 'Enquiry deleted.');
    }

    /** Queue the GoHighLevel sync again (for failed or pending syncs). */
    public function retryGhl(Enquiry $enquiry)
    {
        if (! ghl_enabled()) {
            return back()->withErrors(['ghl' => 'GoHighLevel is switched off. Add GHL_API_TOKEN and GHL_LOCATION_ID to .env first.']);
        }
        $enquiry->update(['ghl_status' => 'pending', 'ghl_error' => null]);
        SyncEnquiryToGhl::dispatch($enquiry->id);

        return back()->with('status', 'GoHighLevel sync queued.');
    }

    public function export(Request $request)
    {
        $rows = $this->filtered($request)->get();
        $cols = ['id', 'created_at', 'status', 'form_name', 'first_name', 'last_name', 'email', 'phone_e164', 'package_name', 'travel_date', 'second_quote', 'special_request', 'consent_given', 'terms_version', 'consented_at', 'ip_address', 'page_url', 'ghl_status'];

        return response()->streamDownload(function () use ($rows, $cols) {
            $out = fopen('php://output', 'w');
            fputcsv($out, array_merge($cols, ['quiz_answers', 'utm_source_first', 'utm_medium_first', 'utm_campaign_first', 'gclid_first']));
            foreach ($rows as $r) {
                $line = array_map(fn ($c) => $this->safeCsv($r->{$c}), $cols);
                $line[] = $this->safeCsv(json_encode($r->quiz_answers));
                foreach (['utm_source', 'utm_medium', 'utm_campaign', 'gclid'] as $k) {
                    $line[] = $this->safeCsv(data_get($r->tracking_first, $k));
                }
                fputcsv($out, $line);
            }
            fclose($out);
        }, 'enquiries-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    /** Prevent spreadsheet formula injection from user-entered text. */
    private function safeCsv(mixed $v): string
    {
        $v = (string) $v;

        return preg_match('/^[=+\-@\t\r]/', $v) ? "'".$v : $v;
    }
}
