<?php

namespace App\Providers;

use App\Support\Tenancy\CurrentStore;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // One tenant context per request/job (scoped => reset between queue jobs / Octane requests)
        $this->app->scoped(CurrentStore::class);
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191); // older MySQL/MariaDB on shared hosting (utf8mb4 index limit)
        Paginator::defaultView('partials.pagination');
    }
}
