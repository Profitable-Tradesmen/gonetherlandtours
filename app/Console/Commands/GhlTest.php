<?php

namespace App\Console\Commands;

use App\Models\Enquiry;
use App\Services\Ghl\GhlService;
use Illuminate\Console\Command;

class GhlTest extends Command
{
    protected $signature = 'ghl:test {--keep : Do not delete the test contact afterwards}';

    protected $description = 'Check the GHL token and location, then upsert (and remove) a clearly labelled test contact';

    public function handle(GhlService $ghl): int
    {
        if (! $ghl->enabled()) {
            $this->error('GHL is OFF. Set GHL_API_TOKEN and GHL_LOCATION_ID in .env first.');

            return self::FAILURE;
        }

        try {
            $this->info('Location OK: '.$ghl->checkLocation());

            $test = new Enquiry([
                'form_name' => 'contact', 'first_name' => 'GHL TEST', 'last_name' => 'Safe to delete',
                'email' => 'ghl-test-'.time().'@example.invalid', 'phone_e164' => '+15555550100',
                'consent_given' => true, 'consented_at' => now(), 'terms_version' => 'test',
                'special_request' => 'Created by php artisan ghl:test',
                'tracking_first' => ['utm_source' => 'ghl-test', 'utm_medium' => 'test'],
                'tracking_last' => ['utm_source' => 'ghl-test', 'utm_medium' => 'test'],
            ]);
            $id = $ghl->upsert($test);
            $this->info("Test contact upserted: $id");

            if ($this->option('keep')) {
                $this->warn('Kept the test contact. Delete it in GHL when finished.');
            } else {
                $this->line($ghl->deleteContact($id) ? 'Test contact removed.' : 'Could not remove the test contact. Please delete "GHL TEST" in GHL.');
            }
        } catch (\Throwable $e) {
            $this->error('GHL test failed: '.$e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
