<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Store;
use App\Models\Theme;
use Illuminate\Http\Request;

class PlatformController extends Controller
{
    public function home()
    {
        return view('platform.home', [
            'plans' => Plan::where('is_active', true)->orderBy('sort')->get(),
            'themes' => Theme::where('is_active', true)->get(),
            'storesCount' => Store::where('status', 'active')->count(),
        ]);
    }

    public function pricing()
    {
        return view('platform.pricing', ['plans' => Plan::where('is_active', true)->orderBy('sort')->get()]);
    }

    public function slugCheck(Request $request)
    {
        $slug = strtolower((string) $request->query('slug'));
        $valid = preg_match('/^[a-z0-9][a-z0-9-]{2,30}$/', $slug) && ! in_array($slug, Store::RESERVED_SLUGS, true);

        return response()->json(['available' => $valid && ! Store::withTrashed()->where('slug', $slug)->exists()]);
    }
}
