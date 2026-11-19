<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SyncEnquiryToSheets;
use App\Models\Enquiry;
use App\Models\Setting;
use App\Services\Sheets\SheetsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

/** Admin > Google Sheets: send every enquiry (with its UTM parameters) to a Google Sheet through an Apps Script web app. */
class SheetsController extends Controller
{
    public function edit()
    {
        $secret = sheets_secret();
        $counts = Enquiry::query()->selectRaw('sheets_status as s, count(*) as n')->groupBy('sheets_status')->pluck('n', 's');

        return view('admin.sheets', [
            'url' => (string) sheets_url(),
            'secretHint' => $secret ? substr($secret, -4) : null,
            'sendEnabled' => setting('sheets_enabled', '1') !== '0',
            'active' => sheets_enabled(),
            'synced' => (int) ($counts['synced'] ?? 0),
            'failed' => (int) ($counts['failed'] ?? 0),
            'pending' => (int) ($counts['pending'] ?? 0),
            'skipped' => (int) ($counts['off'] ?? 0),
            'script' => (string) file_get_contents(resource_path('sheets/apps-script.gs')),
            'headers' => SheetsService::headers(),
            'brand' => setting('brand_name', 'GoNetherlandTours'),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'web_app_url' => ['nullable', 'string', 'max:300', 'regex:'.SheetsService::URL_PATTERN],
            'secret_key' => ['nullable', 'string', 'min:16', 'max:100', 'regex:/^[A-Za-z0-9_-]+$/'],
        ], [
            'web_app_url.regex' => 'That is not a Google Apps Script web app address. It looks like https://script.google.com/macros/s/.../exec',
            'secret_key.min' => 'The secret key must be at least 16 characters. Click Generate to make a strong one.',
            'secret_key.regex' => 'The secret key may only contain letters, numbers, dashes and underscores. Click Generate to make one.',
        ]);

        Setting::put('sheets_url', trim((string) ($data['web_app_url'] ?? '')));

        $secret = trim((string) ($data['secret_key'] ?? ''));
        if ($request->boolean('remove_secret')) {
            Setting::put('sheets_secret', '');
        } elseif ($secret !== '') {
            Setting::put('sheets_secret', Crypt::encryptString($secret));
        }

        Setting::put('sheets_enabled', $request->boolean('enabled') ? '1' : '0');

        return redirect()->route('admin.sheets')->with('status', 'Google Sheets settings saved.');
    }

    /** Sends one clearly labelled sample row (not saved as an enquiry) to check the whole chain. */
    public function test(SheetsService $sheets)
    {
        if (blank(sheets_url()) || blank(sheets_secret())) {
            return redirect()->route('admin.sheets')->withErrors(['sheets' => 'Save the web app address and the secret key first.']);
        }
        try {
            $sheets->sendTest();
        } catch (\Throwable $e) {
            return redirect()->route('admin.sheets')->withErrors(['sheets' => 'The test row was not added. '.$this->safeMessage($e)]);
        }

        return redirect()->route('admin.sheets')->with('status', 'A test row was added to your sheet. Open the "Enquiries" tab to see it, then delete it.');
    }

    /** Queues the enquiries that are not in the sheet yet (received while it was off, or that failed). Limited per click. */
    public function backfill()
    {
        if (! sheets_enabled()) {
            return redirect()->route('admin.sheets')->withErrors(['sheets' => 'Turn Google Sheets on first (web app address, secret key and the tick box).']);
        }
        $limit = config('queue.default') === 'sync' ? 20 : 200;   // with no background worker each row is sent while this page loads
        $todo = Enquiry::whereIn('sheets_status', ['off', 'failed'])->orderBy('id');
        $remaining = (clone $todo)->count();
        $ids = $todo->limit($limit)->pluck('id');
        foreach ($ids as $id) {
            Enquiry::whereKey($id)->update(['sheets_status' => 'pending', 'sheets_error' => null]);
            SyncEnquiryToSheets::dispatch($id);
        }

        $left = max(0, $remaining - $ids->count());

        return redirect()->route('admin.sheets')->with('status', $ids->count().' enquiries sent to the sheet.'.($left ? ' '.$left.' more are waiting: click the button again.' : ''));
    }

    /** Message only, shortened, with the secret key removed so it can never leak into the page. */
    private function safeMessage(\Throwable $e): string
    {
        return Str::limit(str_replace((string) sheets_secret(), '[secret]', $e->getMessage()), 300);
    }
}
