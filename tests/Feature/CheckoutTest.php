<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Coupon;
use App\Models\MessageLog;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ShippingZone;
use App\Models\StockLevel;
use App\Models\Store;
use App\Models\StorePaymentMethod;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\InventoryService;
use App\Services\ShippingCalculator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    protected Store $store;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->store = $this->makeStore('nile', 'basic');
        $this->product = $this->inStore($this->store, function () {
            StorePaymentMethod::where('driver', 'mbok')->update(['is_active' => true, 'config' => ['account_number' => '111', 'account_name' => 'Nile']]);
            ShippingZone::create(['state' => 'الخرطوم', 'city' => 'بحري', 'price' => 2000]);
            ShippingZone::create(['state' => 'الخرطوم', 'city' => 'بحري', 'area' => 'الكدرو', 'price' => 4000]);
            $p = Product::create(['name' => 'جلابية', 'slug' => 'jalabiya', 'price' => 50000, 'cost' => 30000]);
            app(InventoryService::class)->set($p, 5);

            return $p;
        });
    }

    protected function checkout(array $overrides = [])
    {
        $this->post($this->shop($this->store, '/cart'), ['product_id' => $this->product->id, 'qty' => 2]);

        return $this->post($this->shop($this->store, '/checkout'), array_merge([
            'name' => 'محمد', 'phone' => '0911223344', 'fulfillment' => 'delivery', 'state' => 'الخرطوم', 'city' => 'بحري',
            'area' => 'الصافية', 'address' => 'شارع المعونة', 'landmark' => 'جنب الجامع', 'payment_method' => 'cod',
        ], $overrides));
    }

    public function test_shipping_uses_most_specific_zone(): void
    {
        $this->inStore($this->store, function () {
            $calc = app(ShippingCalculator::class);
            $this->assertEquals(4000, (float) $calc->zoneFor('الخرطوم', 'بحري', 'الكدرو')->price);
            $this->assertEquals(2000, (float) $calc->zoneFor('الخرطوم', 'بحري', 'الصافية')->price);
            $this->assertEquals(3000, (float) $calc->zoneFor('الخرطوم', 'أم درمان')->price); // provisioned state-wide default
            $this->assertNull($calc->zoneFor('كسلا', 'كسلا'));
        });
    }

    public function test_cod_order_deducts_stock_notifies_and_credits_on_delivery(): void
    {
        $res = $this->checkout(['coupon' => '']);
        $order = Order::withoutGlobalScopes()->first();
        $res->assertRedirect($this->shop($this->store, '/track/'.$order->tracking_token));

        $this->assertEquals(102000, (float) $order->total); // 2×50,000 + 2,000 shipping
        $this->assertSame('pending', $order->status);
        $this->assertSame('249911223344', $order->ship_phone);
        $this->assertEquals(3, StockLevel::withoutGlobalScopes()->where('product_id', $this->product->id)->sum('quantity'));
        $this->assertTrue(MessageLog::where('to', '249911223344')->exists(), 'customer WhatsApp sent');

        $this->get($this->shop($this->store, '/track/'.$order->tracking_token))->assertOk()->assertSee($order->number);

        $this->actingAs($this->store->owner)->post($this->platform('/dashboard/orders/'.$order->id.'/status'), ['status' => 'delivered'])->assertRedirect();
        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertEquals(1020, (float) $order->commission); // basic plan 1%
        $this->assertEquals(-1020, (float) Wallet::where('store_id', $this->store->id)->value('balance'));
    }

    public function test_cancel_restores_stock(): void
    {
        $this->checkout();
        $order = Order::withoutGlobalScopes()->first();
        $this->actingAs($this->store->owner)->post($this->platform('/dashboard/orders/'.$order->id.'/status'), ['status' => 'cancelled']);
        $this->assertEquals(5, StockLevel::withoutGlobalScopes()->where('product_id', $this->product->id)->sum('quantity'));
    }

    public function test_cannot_order_more_than_stock(): void
    {
        $this->post($this->shop($this->store, '/cart'), ['product_id' => $this->product->id, 'qty' => 9])->assertSessionHas('error');
    }

    public function test_coupon_discount_and_validation(): void
    {
        $this->inStore($this->store, fn () => Coupon::create(['code' => 'EID', 'type' => 'percent', 'value' => 10, 'max_discount' => 5000]));
        $this->checkout(['coupon' => 'eid']);
        $order = Order::withoutGlobalScopes()->first();
        $this->assertEquals(5000, (float) $order->discount);
        $this->assertEquals(97000, (float) $order->total);

        $this->checkout(['coupon' => 'NOPE'])->assertSessionHasErrors('coupon');
    }

    public function test_mbok_receipt_flow_with_duplicate_detection_and_approval(): void
    {
        Storage::fake('local');
        $this->checkout(['payment_method' => 'mbok']);
        $order = Order::withoutGlobalScopes()->first();
        $this->assertSame('awaiting_payment', $order->status);

        $img = UploadedFile::fake()->image('receipt.jpg');
        $this->post($this->shop($this->store, '/track/'.$order->tracking_token.'/receipt'), ['reference' => 'TX123', 'receipt' => $img])->assertSessionHasNoErrors();
        $order->refresh();
        $this->assertSame('pending_review', $order->payment_status);
        $this->assertSame('payment_review', $order->status);

        // second order re-using the same transaction id gets flagged
        $this->checkout(['payment_method' => 'mbok']);
        $second = Order::withoutGlobalScopes()->latest('id')->first();
        $this->post($this->shop($this->store, '/track/'.$second->tracking_token.'/receipt'), ['reference' => 'TX123', 'receipt' => UploadedFile::fake()->image('r2.jpg')]);
        $flagged = Payment::withoutGlobalScopes()->where('order_id', $second->id)->first();
        $this->assertContains('duplicate_reference', $flagged->gateway_payload['flags']);

        $payment = Payment::withoutGlobalScopes()->where('order_id', $order->id)->first();
        $this->actingAs($this->store->owner)->post($this->platform('/dashboard/payments/'.$payment->id.'/approve'))->assertRedirect();
        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('confirmed', $order->status);

        // approving twice is idempotent (no double ledger entries)
        $this->actingAs($this->store->owner)->post($this->platform('/dashboard/payments/'.$payment->id.'/approve'));
        $this->assertEquals(1, WalletTransaction::where('type', 'sale')->count());
    }

    public function test_pickup_order_has_no_shipping_fee(): void
    {
        $branch = $this->inStore($this->store, fn () => Branch::first());
        $this->checkout(['fulfillment' => 'pickup', 'branch_id' => $branch->id, 'state' => null, 'address' => null]);
        $this->assertEquals(100000, (float) Order::withoutGlobalScopes()->first()->total);
    }

    public function test_shipment_waybill_is_generated(): void
    {
        $this->checkout();
        $order = Order::withoutGlobalScopes()->first();
        $this->actingAs($this->store->owner)->post($this->platform('/dashboard/orders/'.$order->id.'/shipment'), ['driver_name' => 'عثمان', 'driver_phone' => '0900000001'])
            ->assertRedirect($this->platform('/dashboard/orders/'.$order->id.'/waybill'));
        $this->get($this->platform('/dashboard/orders/'.$order->id.'/waybill'))->assertOk()->assertSee('عثمان')->assertSee('102,000');
        $this->assertSame('shipped', $order->fresh()->status);
    }
}
