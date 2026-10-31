<?php

namespace App\Models\Concerns;

use App\Models\Store;
use App\Support\Tenancy\CurrentStore;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Shared-database multi-tenancy: rows are isolated by store_id.
 * When a store is active, all queries are filtered and new rows are stamped.
 */
trait BelongsToStore
{
    public static function bootBelongsToStore(): void
    {
        static::addGlobalScope('store', function (Builder $builder) {
            $storeId = app(CurrentStore::class)->id();
            if ($storeId !== null) {
                $builder->where($builder->getModel()->getTable().'.store_id', $storeId);
            }
        });

        static::creating(function ($model) {
            if (empty($model->store_id)) {
                $model->store_id = app(CurrentStore::class)->id();
            }
            if (empty($model->store_id)) {
                throw new \LogicException(static::class.' requires a store context (store_id).');
            }
        });
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function scopeWithoutStoreScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('store');
    }
}
