<?php

namespace App\Http\Middleware;

use App\Models\Store;
use App\Support\Tenancy\CurrentStore;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Merchant dashboard lives on the platform domain (/dashboard). The active store is kept in the session
 * so one user may own/work in several stores. Optional {permission} argument for RBAC.
 */
class MerchantContext
{
    public function __construct(protected CurrentStore $tenancy) {}

    public function handle(Request $request, Closure $next, ?string $permission = null): Response
    {
        $user = $request->user();
        $storeId = session('active_store_id') ?? $user->stores()->value('stores.id');
        $store = $storeId ? Store::with('plan', 'domains')->find($storeId) : null;

        if (! $store || ! $user->roleIn($store)) {
            return redirect()->route('register.store');
        }
        if ($permission && ! $user->canInStore($store, $permission)) {
            abort(403, 'ليس لديك صلاحية للوصول لهذا القسم');
        }

        session(['active_store_id' => $store->id]);
        $this->tenancy->set($store);
        view()->share('store', $store);

        return $next($request);
    }
}
