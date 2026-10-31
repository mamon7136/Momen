<?php

namespace App\Services;

use App\Models\ShippingZone;

/**
 * Most-specific-match zone pricing: area > city > state.
 * e.g. "الخرطوم/بحري/الكدرو" beats "الخرطوم/بحري" beats "الخرطوم".
 */
class ShippingCalculator
{
    public function zoneFor(string $state, ?string $city = null, ?string $area = null): ?ShippingZone
    {
        return ShippingZone::where('is_active', true)
            ->where('state', $state)
            ->where(fn ($q) => $q->whereNull('city')->orWhere('city', $city))
            ->where(fn ($q) => $q->whereNull('area')->orWhere('area', $area))
            ->get()
            ->sortByDesc(fn ($z) => ($z->city ? 2 : 0) + ($z->area ? 1 : 0))
            ->first();
    }

    public function quote(ShippingZone $zone, float $subtotal, float $weightKg = 0): float
    {
        if ($zone->free_over && $subtotal >= (float) $zone->free_over) {
            return 0.0;
        }

        return round((float) $zone->price + max(0, $weightKg - 1) * (float) $zone->price_per_kg, 2);
    }
}
