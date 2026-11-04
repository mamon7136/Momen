<?php

namespace App\Services;

use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VisitTracker
{
    public function hit(Store $store, Request $request): void
    {
        if (! $request->isMethod('GET') || $request->expectsJson() || preg_match('/(sw\.js|manifest|offline)$/', $request->path())) {
            return;
        }
        $key = 'visited.'.$store->id.'.'.today()->toDateString();
        $isProduct = str_contains($request->path(), '/p/') || str_starts_with($request->path(), 'p/');
        if (session()->has($key) && ! $isProduct) {
            return;
        }

        try {
            $row = ['store_id' => $store->id, 'date' => today()->toDateString()];
            DB::table('store_daily_stats')->insertOrIgnore($row + ['visits' => 0, 'product_views' => 0]);
            $q = DB::table('store_daily_stats')->where($row);
            if (! session()->has($key)) {
                $q->increment('visits');
                session([$key => true]);
            }
            if ($isProduct) {
                DB::table('store_daily_stats')->where($row)->increment('product_views');
            }
        } catch (\Throwable) {
            // analytics must never break the storefront
        }
    }

    public static function visits(int $storeId, $from, $to): int
    {
        return (int) DB::table('store_daily_stats')->where('store_id', $storeId)->whereBetween('date', [$from->toDateString(), $to->toDateString()])->sum('visits');
    }
}
