<?php

namespace App\Http\Middleware;

use App\Models\Store;
use App\Models\StoreDomain;
use App\Support\Tenancy\CurrentStore;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the store from:
 *   1. /s/{store_slug}/...           (path mode – works on any shared hosting)
 *   2. {slug}.platform.sd            (wildcard subdomain)
 *   3. shop.merchant.com             (verified custom domain)
 */
class IdentifyStore
{
    public function __construct(protected CurrentStore $tenancy) {}

    public function handle(Request $request, Closure $next): Response
    {
        $mode = 'host';
        if ($slug = $request->route('store_slug')) {
            $mode = 'path';
            $request->route()->forgetParameter('store_slug');
            $storeId = Cache::remember('store-slug:'.$slug, 300, fn () => Store::where('slug', $slug)->value('id'));
        } else {
            $host = strtolower($request->getHost());
            $root = strtolower(config('platform.domain'));
            $storeId = Cache::remember('store-host:'.$host, 300, function () use ($host, $root) {
                if (str_ends_with($host, '.'.$root)) {
                    return Store::where('slug', substr($host, 0, -strlen('.'.$root)))->value('id');
                }

                return StoreDomain::where('domain', $host)->whereNotNull('verified_at')->value('store_id');
            });
        }

        $store = $storeId ? Store::with(['theme', 'domains'])->find($storeId) : null;
        abort_if(! $store, 404, 'المتجر غير موجود');

        $this->tenancy->set($store, $mode);
        view()->share('store', $store);

        if (! $store->isOpen() && ! $request->user()?->canInStore($store, 'products')) {
            return response()->view('storefront.closed', ['store' => $store], 503);
        }

        return $next($request);
    }
}
