<?php

namespace App\Http\Controllers;

use App\Http\Requests\EnquiryRequest;
use App\Services\EnquiryService;
use App\Support\Seo;
use Illuminate\Http\Request;

class EnquiryController extends Controller
{
    public function store(EnquiryRequest $request, EnquiryService $service)
    {
        $enquiry = $service->create($request->validated(), $request);

        // Flash for the Thank You page (fires the conversion event once).
        $request->session()->flash('enquiry', [
            'id' => $enquiry->id, 'name' => $enquiry->first_name, 'form' => $enquiry->form_name,
        ]);

        return $request->expectsJson()
            ? response()->json(['ok' => true, 'redirect' => route('thank-you')])
            : redirect()->route('thank-you');
    }

    public function thanks(Request $request)
    {
        $e = $request->session()->get('enquiry');

        return view('pages.thank-you', [
            'name' => $e['name'] ?? null,
            'enquiryId' => $e['id'] ?? null,
            'formName' => $e['form'] ?? null,
            'seo' => Seo::make(['title' => 'Thank you | '.setting('brand_name', 'GoNetherlandTours'), 'noindex' => true]),
        ]);
    }
}
