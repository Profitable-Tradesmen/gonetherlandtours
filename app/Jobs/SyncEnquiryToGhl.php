<?php

namespace App\Jobs;

use App\Models\Enquiry;
use App\Services\Ghl\GhlService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/** Pushes one enquiry to GoHighLevel. Retries with backoff and never fails the enquiry itself. */
class SyncEnquiryToGhl implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public function __construct(public int $enquiryId) {}

    public function backoff(): array
    {
        return [60, 300, 900, 3600];
    }

    public function handle(GhlService $ghl): void
    {
        $enquiry = Enquiry::find($this->enquiryId);
        if (! $enquiry) {
            return;
        }
        if (! $ghl->enabled()) {
            $enquiry->update(['ghl_status' => 'off']);

            return;
        }

        try {
            $contactId = $ghl->upsert($enquiry);
            $enquiry->update(['ghl_status' => 'synced', 'ghl_contact_id' => $contactId, 'ghl_error' => null, 'ghl_synced_at' => now()]);
        } catch (\Throwable $e) {
            // Message only: never log request headers, so the token cannot leak.
            $message = Str::limit($e->getMessage(), 500);
            Log::warning('GHL sync failed for enquiry '.$enquiry->id.': '.$message);
            $final = config('queue.default') === 'sync' || $this->attempts() >= $this->tries;
            $enquiry->update(['ghl_status' => $final ? 'failed' : 'pending', 'ghl_error' => $message]);
            if (! $final) {
                throw $e;   // let the queue retry with backoff
            }
        }
    }
}
