<?php

namespace App\Support\Tenancy;

use App\Models\Store;

/**
 * Holds the tenant (store) resolved for the current request / job.
 * Every model using BelongsToStore is automatically scoped to it.
 */
class CurrentStore
{
    protected ?Store $store = null;

    /** 'host' (subdomain/custom domain) or 'path' (/s/{slug}) */
    protected string $mode = 'host';

    public function set(?Store $store, string $mode = 'host'): void
    {
        $this->store = $store;
        $this->mode = $mode;
    }

    public function get(): ?Store
    {
        return $this->store;
    }

    public function id(): ?int
    {
        return $this->store?->id;
    }

    public function check(): bool
    {
        return $this->store !== null;
    }

    public function mode(): string
    {
        return $this->mode;
    }

    public function forget(): void
    {
        $this->store = null;
    }

    /** Run a callback in the context of another store (jobs, admin tools). */
    public function runAs(Store $store, callable $callback): mixed
    {
        [$prev, $prevMode] = [$this->store, $this->mode];
        $this->set($store, $this->mode);
        try {
            return $callback($store);
        } finally {
            $this->set($prev, $prevMode);
        }
    }
}
