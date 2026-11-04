<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\Theme;
use App\Services\InventoryService;
use App\Services\OrderService;
use App\Support\Tenancy\CurrentStore;
use Tests\TestCase;

class ThemesTest extends TestCase
{
    public function test_every_theme_renders_storefront_pages(): void
    {
        $store = $this->makeStore('themed');
        $product = $this->inStore($store, function () {
            $p = Product::create(['name' => 'ساعة', 'slug' => 'watch', 'price' => 1000, 'is_featured' => true]);
            app(InventoryService::class)->set($p, 3);

            return $p;
        });

        foreach (Theme::where('is_active', true)->get() as $theme) {
            $store->update(['theme_id' => $theme->id, 'theme_settings' => null]);
            cache()->flush();
            $this->get($this->shop($store))->assertOk()->assertSee('themes/'.$theme->slug.'/theme.css', false)->assertSee('ساعة');
            $this->get($this->shop($store, '/p/watch'))->assertOk();
            $this->get($this->shop($store, '/products'))->assertOk();
            $this->post($this->shop($store, '/cart'), ['product_id' => $product->id]);
            $this->get($this->shop($store, '/checkout'))->assertOk();
        }
    }

    public function test_merchant_can_preview_and_apply_a_theme(): void
    {
        $store = $this->makeStore();
        $oud = Theme::where('slug', 'oud')->first();

        $this->get($this->shop($store, '/?preview_theme=oud'))->assertDontSee('themes/oud/theme.css', false); // guests can't preview
        $this->actingAs($store->owner)->get($this->shop($store, '/?preview_theme=oud'))->assertSee('themes/oud/theme.css', false);
        $this->actingAs($store->owner)->get($this->platform('/dashboard/themes'))->assertOk()->assertSee('عود');

        $this->actingAs($store->owner)->post($this->platform('/dashboard/design/theme'), ['theme_id' => $oud->id])->assertRedirect();
        $this->assertSame($oud->id, $store->fresh()->theme_id);
    }

    public function test_merchant_sections_replace_theme_sections_entirely(): void
    {
        $store = $this->makeStore();
        $store->update(['theme_settings' => ['sections' => [['type' => 'text', 'title' => 'only']]]]);
        $this->assertCount(1, $store->fresh()->themeConfig()['sections']);
    }

    public function test_dashboard_home_with_orders_and_quick_product_edit(): void
    {
        $store = $this->makeStore();
        $p = $this->inStore($store, function () use ($store) {
            $p = Product::create(['name' => 'قلم', 'slug' => 'pen', 'price' => 500]);
            app(InventoryService::class)->set($p, 10);
            app(CurrentStore::class)->set($store);
            app(OrderService::class)->place(collect([['line' => 'x', 'product' => $p, 'variant' => null, 'qty' => 1, 'price' => 500.0, 'total' => 500.0]]),
                ['name' => 'علي', 'phone' => '0911111111', 'fulfillment' => 'delivery', 'state' => 'الخرطوم', 'address' => 'x', 'payment_method' => 'cod']);

            return $p;
        });

        $this->actingAs($store->owner)->get($this->platform('/dashboard'))->assertOk()->assertSee('علي');
        $this->actingAs($store->owner)->get($this->platform('/dashboard/reports'))->assertOk();
        $this->actingAs($store->owner)->post($this->platform('/dashboard/products/'.$p->id.'/quick'), ['name' => 'قلم حبر', 'price' => 750, 'stock' => 4])->assertRedirect();
        $p = Product::withoutGlobalScopes()->find($p->id);
        $this->assertSame('قلم حبر', $p->name);
        $this->assertEquals(4, $p->availableStock());
        $this->assertSame(1, Order::withoutGlobalScopes()->count());
    }
}
