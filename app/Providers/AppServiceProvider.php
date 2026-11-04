<?php

namespace App\Providers;

use App\Models\Category;
use App\Services\CartService;
use App\Support\Tenancy\CurrentStore;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
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

        // Storefront theme data shared by every theme template (layout, sections, cards)
        View::composer(['themes.*', 'storefront.*'], function ($view) {
            $store = current_store();
            if (! $store) {
                return;
            }
            $attrs = request()->attributes;
            $shared = $attrs->get('theme.shared') ?? [
                'cfg' => $store->themeConfig(),
                'slug' => $store->theme?->slug ?? 'nile',
                'cartCount' => app(CartService::class)->count(),
                'navCategories' => Category::whereNull('parent_id')->orderBy('sort')->limit(8)->get(),
            ];
            $attrs->set('theme.shared', $shared);
            $view->with($shared);
        });
    }
}
