<?php

namespace App\Jobs;

use App\Models\Enquiry;
use App\Services\Sheets\SheetsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/** Adds one enquiry as a row in the Google Sheet. Retries with backoff and never fails the enquiry itself. */
class SyncEnquiryToSheets implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public function __construct(public int $enquiryId) {}

    public function backoff(): array
    {
        return [60, 300, 900, 3600];
    }

    public function handle(SheetsService $sheets): void
    {
        $enquiry = Enquiry::find($this->enquiryId);
        if (! $enquiry) {
            return;
        }
        if (! $sheets->enabled()) {
            $enquiry->update(['sheets_status' => 'off']);

            return;
        }

        try {
            $sheets->send($enquiry);
            $enquiry->update(['sheets_status' => 'synced', 'sheets_error' => null, 'sheets_synced_at' => now()]);
        } catch (\Throwable $e) {
            // Message only: the secret key is never logged.
            $message = Str::limit(str_replace((string) sheets_secret(), '[secret]', $e->getMessage()), 500);
            Log::warning('Google Sheets sync failed for enquiry '.$enquiry->id.': '.$message);
            $final = config('queue.default') === 'sync' || $this->attempts() >= $this->tries;
            $enquiry->update(['sheets_status' => $final ? 'failed' : 'pending', 'sheets_error' => $message]);
            if (! $final) {
                throw $e;   // let the queue retry with backoff
            }
        }
    }
}
