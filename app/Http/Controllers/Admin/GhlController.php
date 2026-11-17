<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\Ghl\GhlFields;
use App\Services\Ghl\GhlService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

/** Admin > GoHighLevel: connect the CRM with a Location ID + Private Integration token (token stored encrypted). */
class GhlController extends Controller
{
    public function edit()
    {
        $token = ghl_token();

        return view('admin.ghl', [
            'locationId' => ghl_location_id(),
            'tokenHint' => $token ? substr($token, -4) : null,
            'fromEnv' => blank(setting('ghl_token')) && filled(config('services.ghl.token')),
            'sendEnabled' => setting('ghl_enabled', '1') !== '0',
            'active' => ghl_enabled(),
            'fieldCount' => count(GhlFields::all()),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'location_id' => ['nullable', 'string', 'max:64', 'regex:/^[A-Za-z0-9]+$/'],
            'api_token' => ['nullable', 'string', 'max:500'],
        ], ['location_id.regex' => 'The Location ID may only contain letters and numbers.']);

        Setting::put('ghl_location_id', trim((string) ($data['location_id'] ?? '')));

        $token = trim((string) ($data['api_token'] ?? ''));
        if ($request->boolean('remove_token')) {
            Setting::put('ghl_token', '');
        } elseif ($token !== '') {
            Setting::put('ghl_token', Crypt::encryptString($token));
        }

        Setting::put('ghl_enabled', $request->boolean('enabled') ? '1' : '0');
        Cache::forget('ghl.field_ids');

        return redirect()->route('admin.ghl')->with('status', 'GoHighLevel settings saved.');
    }

    /** Read-only: checks token, Location ID and the custom fields. Creates nothing, sends no contact. */
    public function test(GhlService $ghl)
    {
        if (blank(ghl_token()) || blank(ghl_location_id())) {
            return redirect()->route('admin.ghl')->withErrors(['ghl' => 'Save a Location ID and an API token first.']);
        }
        try {
            $r = $ghl->diagnose();
        } catch (\Throwable $e) {
            return redirect()->route('admin.ghl')->withErrors(['ghl' => 'Connection failed: '.$this->safeMessage($e)]);
        }

        $msg = 'Connected to "'.$r['location'].'". ';
        $msg .= $r['missing']
            ? count($r['missing']).' of '.$r['total'].' custom fields are missing. Click "Create missing custom fields". Missing: '.implode(', ', array_slice($r['missing'], 0, 8)).(count($r['missing']) > 8 ? ', ...' : '').'.'
            : 'All '.$r['total'].' custom fields exist.';

        return redirect()->route('admin.ghl')->with('status', $msg);
    }

    public function setup(GhlService $ghl)
    {
        if (blank(ghl_token()) || blank(ghl_location_id())) {
            return redirect()->route('admin.ghl')->withErrors(['ghl' => 'Save a Location ID and an API token first.']);
        }
        try {
            $r = $ghl->setupFields();
        } catch (\Throwable $e) {
            return redirect()->route('admin.ghl')->withErrors(['ghl' => 'Could not create the fields: '.$this->safeMessage($e)]);
        }

        return redirect()->route('admin.ghl')->with('status', count($r['created']).' custom fields created, '.count($r['existing']).' already existed.');
    }

    /** Message only (never headers), shortened, so the token cannot leak into the page. */
    private function safeMessage(\Throwable $e): string
    {
        return Str::limit(str_replace((string) ghl_token(), '[token]', $e->getMessage()), 300);
    }
}
