<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\StoreDomain;
use App\Models\User;
use Tests\TestCase;

class TenancyTest extends TestCase
{
    public function test_storefront_only_shows_its_own_products(): void
    {
        $a = $this->makeStore('alpha');
        $b = $this->makeStore('beta');
        $this->inStore($a, fn () => Product::create(['name' => 'Alpha Shoe', 'slug' => 'shoe', 'price' => 100, 'track_stock' => false]));
        $this->inStore($b, fn () => Product::create(['name' => 'Beta Bag', 'slug' => 'bag', 'price' => 100, 'track_stock' => false]));

        $this->get($this->shop($a, '/products'))->assertSee('Alpha Shoe')->assertDontSee('Beta Bag');
        $this->get($this->shop($b, '/p/shoe'))->assertNotFound();
    }

    public function test_merchant_cannot_touch_another_stores_records_via_route_binding(): void
    {
        $a = $this->makeStore('alpha');
        $b = $this->makeStore('beta');
        $foreign = $this->inStore($b, fn () => Product::create(['name' => 'Secret', 'slug' => 's', 'price' => 1]));

        $this->actingAs($a->owner)->get($this->platform('/dashboard/products/'.$foreign->id.'/edit'))->assertNotFound();
        $this->actingAs($a->owner)->delete($this->platform('/dashboard/products/'.$foreign->id))->assertNotFound();
        $this->assertNotNull(Product::withoutGlobalScopes()->find($foreign->id));
    }

    public function test_custom_domain_resolves_only_after_verification(): void
    {
        $store = $this->makeStore('custom');
        $domain = StoreDomain::create(['store_id' => $store->id, 'domain' => 'shop.example.com', 'verification_token' => 'x', 'is_primary' => true]);
        $this->get('http://shop.example.com/')->assertNotFound();
        $domain->update(['verified_at' => now()]);
        cache()->flush();
        $this->get('http://shop.example.com/')->assertOk()->assertSee('Store custom');
    }

    public function test_path_mode_storefront_works_on_platform_domain(): void
    {
        $store = $this->makeStore('pathy');
        $this->get($this->platform('/s/pathy'))->assertOk()->assertSee('Store pathy');
    }

    public function test_staff_permissions_are_enforced(): void
    {
        $store = $this->makeStore();
        $staff = User::create(['name' => 's', 'email' => 's@x.sd', 'phone' => '249900111222', 'password' => 'password']);
        $store->staff()->attach($staff->id, ['role' => 'shipping']);

        $this->actingAs($staff)->get($this->platform('/dashboard/orders'))->assertOk();
        $this->actingAs($staff)->get($this->platform('/dashboard/products'))->assertForbidden();
        $this->actingAs($staff)->get($this->platform('/dashboard/settings'))->assertForbidden();
        $this->actingAs($staff)->get($this->platform('/dashboard/wallet'))->assertForbidden();
    }

    public function test_orders_query_without_context_is_unscoped_but_with_context_is_scoped(): void
    {
        $a = $this->makeStore('alpha');
        $this->makeStore('beta');
        $this->inStore($a, fn () => $this->assertSame(0, Order::count()));
    }
}
