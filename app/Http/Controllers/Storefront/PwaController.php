<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;

class PwaController extends Controller
{
    public function manifest()
    {
        $store = current_store();
        $color = $store->themeConfig()['colors']['primary'];

        return response()->json([
            'name' => $store->name, 'short_name' => mb_substr($store->name, 0, 12),
            'start_url' => sroute('home'), 'scope' => sroute('home'), 'display' => 'standalone', 'dir' => 'rtl', 'lang' => 'ar',
            'background_color' => '#ffffff', 'theme_color' => $color,
            'icons' => [
                ['src' => $store->logo ? asset('storage/'.$store->logo) : asset('icons/icon-192.png'), 'sizes' => '192x192', 'type' => 'image/png'],
                ['src' => $store->logo ? asset('storage/'.$store->logo) : asset('icons/icon-512.png'), 'sizes' => '512x512', 'type' => 'image/png'],
            ],
        ])->header('Content-Type', 'application/manifest+json');
    }

    public function worker()
    {
        return response()->view('storefront.sw', ['version' => 'v1-'.current_store()->id.'-'.optional(current_store()->updated_at)->timestamp])
            ->header('Content-Type', 'application/javascript')
            ->header('Service-Worker-Allowed', '/');
    }

    public function offline()
    {
        return view('storefront.offline');
    }
}
