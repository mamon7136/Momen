<?php

namespace App\Models;

use App\Models\Concerns\BelongsToStore;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use BelongsToStore, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'track_stock' => 'boolean',
            'price' => 'decimal:2',
            'compare_price' => 'decimal:2',
            'cost' => 'decimal:2',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function stockLevels(): HasMany
    {
        return $this->hasMany(StockLevel::class);
    }

    /** Price in SDG; products may be priced in USD and converted at the live rate. */
    public function priceSdg(?ProductVariant $variant = null): float
    {
        $price = (float) ($variant?->price ?? $this->price);

        return ExchangeRate::toSdg($price, $this->price_currency ?? 'SDG');
    }

    public function availableStock(?int $variantId = null): int
    {
        if (! $this->track_stock || $this->type === 'digital') {
            return PHP_INT_MAX;
        }

        $query = $this->stockLevels();
        // variable product without a chosen variant => total of all variants
        if ($variantId !== null || $this->type !== 'variable') {
            $query->where('variant_id', $variantId);
        }

        return (int) $query->sum('quantity');
    }

    public function isLowStock(): bool
    {
        return $this->track_stock && $this->availableStock() <= $this->low_stock_threshold;
    }

    public function image(): ?string
    {
        $img = $this->images[0] ?? null;

        return $img ? asset('storage/'.$img) : null;
    }
}
