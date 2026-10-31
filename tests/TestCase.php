<?php

namespace Tests;

use App\Models\Plan;
use App\Models\Store;
use App\Models\User;
use App\Services\StoreProvisioner;
use App\Support\Tenancy\CurrentStore;
use Database\Seeders\PlatformSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlatformSeeder::class);
    }

    protected function platform(string $path = '/'): string
    {
        return 'http://platform.test'.$path;
    }

    protected function shop(Store $store, string $path = '/'): string
    {
        return 'http://'.$store->slug.'.platform.test'.$path;
    }

    protected function makeStore(string $slug = 'shop', string $plan = 'pro', ?User $owner = null): Store
    {
        $owner ??= User::create(['name' => 'Owner '.$slug, 'email' => $slug.'@t.sd', 'phone' => '2499'.random_int(10000000, 99999999), 'password' => 'password']);
        $store = app(StoreProvisioner::class)->provision($owner, ['store_name' => 'Store '.$slug, 'slug' => $slug], Plan::where('slug', $plan)->first());
        $store->update(['status' => 'active', 'subscription_ends_at' => now()->addMonth()]);
        app(CurrentStore::class)->forget();

        return $store->fresh();
    }

    /** Run code inside a store context then clear it (so HTTP requests resolve their own tenant). */
    protected function inStore(Store $store, callable $fn): mixed
    {
        $result = app(CurrentStore::class)->runAs($store, $fn);
        app(CurrentStore::class)->forget();

        return $result;
    }
}
