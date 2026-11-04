<?php

use App\Http\Middleware\IdentifyStore;
use App\Http\Middleware\MerchantContext;
use App\Http\Middleware\SuperAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        using: function () {
            $platform = config('platform.domain');

            // PLATFORM_DOMAIN=* => "any host" trial mode: platform + /s/{slug} stores on whatever
            // domain/IP the site is opened from (no wildcard DNS, no custom domains).
            if ($platform === '*') {
                Route::middleware('web')->group(base_path('routes/web.php'));
                Route::middleware(['web', IdentifyStore::class])->prefix('s/{store_slug}')->name('path.store.')
                    ->where(['store_slug' => '[a-z0-9-]+'])->group(base_path('routes/storefront.php'));

                return;
            }

            // 1) Platform domain: landing, auth, merchant dashboard, super admin, webhooks
            Route::middleware('web')->domain($platform)->group(base_path('routes/web.php'));
            Route::middleware('web')->domain('www.'.$platform)->get('/', fn () => redirect()->away(config('platform.scheme').'://'.$platform));

            // 2) Path-mode storefront on the platform domain: platform.sd/s/{slug}
            Route::middleware(['web', IdentifyStore::class])->domain($platform)
                ->prefix('s/{store_slug}')->name('path.store.')
                ->where(['store_slug' => '[a-z0-9-]+'])
                ->group(base_path('routes/storefront.php'));

            // 3) Any other host => subdomain or custom domain storefront
            Route::middleware(['web', IdentifyStore::class])->name('store.')->group(base_path('routes/storefront.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Behind Cloudflare / hosting proxies: honour X-Forwarded-Proto so generated links stay https
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'merchant' => MerchantContext::class,
            'superadmin' => SuperAdmin::class,
        ]);
        // Tenant must be resolved BEFORE route-model binding so {product}, {order}... are store-scoped.
        $middleware->prependToPriorityList(before: SubstituteBindings::class, prepend: IdentifyStore::class);
        $middleware->prependToPriorityList(before: SubstituteBindings::class, prepend: MerchantContext::class);
        $middleware->validateCsrfTokens(except: ['webhooks/*']);
        $middleware->redirectGuestsTo(fn () => route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
