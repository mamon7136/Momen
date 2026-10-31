<?php

use App\Services\SubscriptionService;
use Illuminate\Support\Facades\Schedule;

// DirectAdmin cron (every minute):  * * * * * cd /home/USER/platform && php artisan schedule:run >> /dev/null 2>&1
Schedule::call(fn () => app(SubscriptionService::class)->sweep())->dailyAt('03:00')->name('subscriptions-sweep');
Schedule::command('queue:work --stop-when-empty --max-time=55 --tries=3')->everyMinute()->withoutOverlapping();
Schedule::command('queue:prune-failed --hours=168')->daily();
