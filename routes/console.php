<?php

use Illuminate\Support\Facades\Schedule;

// Production on shared hosting (no always-on worker): the one cron entry `php artisan schedule:run`
// (every minute) runs this, which drains the queue (emails + GoHighLevel sync) and exits.
// Harmless when QUEUE_CONNECTION=sync (locally). See docs/reference/DEPLOYMENT.md.
Schedule::command('queue:work --stop-when-empty --max-time=50 --tries=5')->everyMinute()->withoutOverlapping();
