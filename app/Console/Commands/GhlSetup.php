<?php

namespace App\Console\Commands;

use App\Services\Ghl\GhlService;
use Illuminate\Console\Command;

class GhlSetup extends Command
{
    protected $signature = 'ghl:setup';

    protected $description = 'Create every GoHighLevel custom field the site writes (skips any that already exist; safe to re-run)';

    public function handle(GhlService $ghl): int
    {
        if (! $ghl->enabled()) {
            $this->error('GHL is OFF. Set GHL_API_TOKEN and GHL_LOCATION_ID in .env first.');

            return self::FAILURE;
        }

        try {
            $result = $ghl->setupFields();
        } catch (\Throwable $e) {
            $this->error('GHL request failed: '.$e->getMessage());

            return self::FAILURE;
        }

        foreach ($result['created'] as $n) {
            $this->line("  <info>created</info>  $n");
        }
        foreach ($result['existing'] as $n) {
            $this->line("  skipped  $n (already exists)");
        }
        $this->info(count($result['created']).' created, '.count($result['existing']).' already existed.');

        return self::SUCCESS;
    }
}
