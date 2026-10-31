<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockLevel;
use App\Models\StockMovement;

class InventoryService
{
    public function defaultBranch(): Branch
    {
        return Branch::where('is_default', true)->first() ?? Branch::firstOrFail();
    }

    public function set(Product $product, int $quantity, ?int $variantId = null, ?Branch $branch = null, ?int $userId = null): StockLevel
    {
        $branch ??= $this->defaultBranch();
        $level = StockLevel::firstOrCreate(
            ['branch_id' => $branch->id, 'product_id' => $product->id, 'variant_id' => $variantId],
            ['quantity' => 0]
        );
        $diff = $quantity - $level->quantity;
        if ($diff !== 0) {
            $level->update(['quantity' => $quantity]);
            StockMovement::create(['store_id' => $level->store_id, 'stock_level_id' => $level->id, 'change' => $diff, 'reason' => 'adjustment', 'user_id' => $userId]);
        }

        return $level;
    }

    /**
     * Deduct stock across branches (preferred branch first). Throws when insufficient.
     * Must be called inside a DB transaction.
     */
    public function deduct(Product $product, ?int $variantId, int $qty, $reference, ?int $preferredBranchId = null): void
    {
        if (! $product->track_stock || $product->type === 'digital') {
            return;
        }
        $levels = StockLevel::where('product_id', $product->id)->where('variant_id', $variantId)
            ->where('quantity', '>', 0)->lockForUpdate()->get()
            ->sortByDesc(fn ($l) => $l->branch_id === $preferredBranchId ? 1 : 0);

        if ($levels->sum('quantity') < $qty) {
            throw new \DomainException("الكمية المطلوبة من \"{$product->name}\" غير متوفرة");
        }
        foreach ($levels as $level) {
            if ($qty <= 0) {
                break;
            }
            $take = min($qty, $level->quantity);
            $level->decrement('quantity', $take);
            StockMovement::create(['store_id' => $level->store_id, 'stock_level_id' => $level->id, 'change' => -$take, 'reason' => 'order',
                'reference_type' => $reference::class, 'reference_id' => $reference->getKey()]);
            $qty -= $take;
        }
    }

    public function restore(Order $order): void
    {
        foreach (StockMovement::where('reference_type', $order::class)->where('reference_id', $order->id)->where('reason', 'order')->get() as $m) {
            StockLevel::whereKey($m->stock_level_id)->increment('quantity', -$m->change);
            StockMovement::create(['store_id' => $m->store_id, 'stock_level_id' => $m->stock_level_id, 'change' => -$m->change, 'reason' => 'cancel',
                'reference_type' => $order::class, 'reference_id' => $order->id]);
        }
    }
}
