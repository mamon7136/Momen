<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;

/** Session cart, namespaced per store so one browser can shop in several stores. */
class CartService
{
    protected function key(): string
    {
        return 'cart.'.current_store()->id;
    }

    public function raw(): array
    {
        return session($this->key(), []);
    }

    public function add(int $productId, ?int $variantId, int $qty = 1): void
    {
        $cart = $this->raw();
        $line = $productId.':'.($variantId ?? 0);
        $cart[$line] = ($cart[$line] ?? 0) + max(1, $qty);
        session([$this->key() => $cart]);
    }

    public function update(string $line, int $qty): void
    {
        $cart = $this->raw();
        if ($qty <= 0) {
            unset($cart[$line]);
        } else {
            $cart[$line] = $qty;
        }
        session([$this->key() => $cart]);
    }

    public function clear(): void
    {
        session()->forget($this->key());
    }

    public function count(): int
    {
        return array_sum($this->raw());
    }

    /** @return Collection<int, array{line:string, product:Product, variant:?ProductVariant, qty:int, price:float, total:float}> */
    public function lines(): Collection
    {
        $raw = $this->raw();
        if (! $raw) {
            return collect();
        }
        $ids = collect(array_keys($raw))->map(fn ($l) => (int) explode(':', $l)[0]);
        $products = Product::with('variants')->where('is_active', true)->whereIn('id', $ids)->get()->keyBy('id');

        return collect($raw)->map(function ($qty, $line) use ($products) {
            [$pid, $vid] = array_map('intval', explode(':', $line));
            $product = $products[$pid] ?? null;
            if (! $product) {
                return null;
            }
            $variant = $vid ? $product->variants->firstWhere('id', $vid) : null;
            $price = $product->priceSdg($variant);

            return compact('line', 'product', 'variant', 'qty', 'price') + ['total' => round($price * $qty, 2)];
        })->filter()->values();
    }

    public function subtotal(): float
    {
        return round($this->lines()->sum('total'), 2);
    }
}
