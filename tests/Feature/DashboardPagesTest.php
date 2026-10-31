<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class DashboardPagesTest extends TestCase
{
    public function test_every_merchant_page_renders(): void
    {
        $store = $this->makeStore();
        foreach (['', '/products', '/products/create', '/categories', '/orders', '/customers', '/payment-methods', '/coupons', '/marketing',
            '/shipping', '/design', '/reports', '/wallet', '/settings', '/staff', '/billing', '/apps'] as $page) {
            $this->actingAs($store->owner)->get($this->platform('/dashboard'.$page))->assertOk();
        }
    }

    public function test_every_admin_page_renders(): void
    {
        $this->makeStore();
        $admin = User::where('is_super_admin', true)->first();
        foreach (['', '/stores', '/subscriptions', '/plans', '/themes', '/payouts', '/settings'] as $page) {
            $this->actingAs($admin)->get($this->platform('/admin'.$page))->assertOk();
        }
    }

    public function test_storefront_pwa_endpoints(): void
    {
        $store = $this->makeStore();
        $this->get($this->shop($store, '/manifest.webmanifest'))->assertOk()->assertJsonPath('dir', 'rtl');
        $this->get($this->shop($store, '/sw.js'))->assertOk()->assertHeader('Content-Type', 'application/javascript');
    }

    public function test_page_builder_saves_sections(): void
    {
        $store = $this->makeStore();
        $this->actingAs($store->owner)->post($this->platform('/dashboard/design'), [
            'colors' => ['primary' => '#112233', 'secondary' => '#445566', 'background' => '#ffffff', 'text' => '#000000'],
            'font' => 'Cairo', 'announcement' => 'توصيل مجاني',
            'sections' => [['type' => 'hero', 'title' => 'عروض رمضان'], ['type' => 'latest_products']],
        ])->assertSessionHasNoErrors();
        $this->get($this->shop($store))->assertSee('عروض رمضان')->assertSee('#112233')->assertSee('توصيل مجاني');
    }
}
