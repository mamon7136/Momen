<?php

use App\Models\Store;
use App\Support\Tenancy\CurrentStore;

if (! function_exists('money')) {
    function money(float|string|null $amount, string $currency = 'SDG'): string
    {
        $symbol = $currency === 'SDG' ? 'ج.س' : $currency;

        return number_format((float) $amount, 0).' '.$symbol;
    }
}

if (! function_exists('current_store')) {
    function current_store(): ?Store
    {
        return app(CurrentStore::class)->get();
    }
}

if (! function_exists('sroute')) {
    /** Storefront route helper that works in both host mode and /s/{slug} path mode. */
    function sroute(string $name, array $params = []): string
    {
        $tenancy = app(CurrentStore::class);
        if ($tenancy->mode() === 'path') {
            return route('path.store.'.$name, ['store_slug' => $tenancy->get()->slug] + $params);
        }

        return route('store.'.$name, $params);
    }
}
