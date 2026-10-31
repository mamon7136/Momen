<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Customers, shipping, coupons, orders, payments. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('phone', 20);
            $table->string('email')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->unsignedInteger('orders_count')->default(0);
            $table->decimal('total_spent', 14, 2)->default(0);
            $table->timestamps();
            $table->unique(['store_id', 'phone']);
        });

        Schema::create('shipping_companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained()->cascadeOnDelete(); // null = platform-provided
            $table->string('name');
            $table->string('driver')->default('manual'); // manual, api (future integrations)
            $table->string('type')->default('courier'); // courier, motorcycle, rickshaw, bus_cargo
            $table->string('tracking_url')->nullable(); // with {tracking} placeholder
            $table->json('config')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shipping_company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('state');              // الولاية
            $table->string('city')->nullable();   // المدينة  (null = whole state)
            $table->string('area')->nullable();   // الحي/المنطقة (null = whole city)
            $table->decimal('price', 14, 2);
            $table->decimal('free_over', 14, 2)->nullable();
            $table->decimal('price_per_kg', 14, 2)->default(0);
            $table->string('eta')->nullable();    // "نفس اليوم", "2-3 أيام"
            $table->boolean('cod_allowed')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['store_id', 'state', 'city']);
        });

        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('type')->default('percent'); // percent, fixed, free_shipping
            $table->decimal('value', 14, 2)->default(0);
            $table->decimal('min_order', 14, 2)->nullable();
            $table->decimal('max_discount', 14, 2)->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('per_customer_limit')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['store_id', 'code']);
        });

        Schema::create('store_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('driver'); // cod, bank_transfer, mbok, syberpay, fawry
            $table->string('label');
            $table->json('config')->nullable(); // account no, account name, merchant id ...
            $table->text('instructions')->nullable();
            $table->decimal('fee_percent', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
            $table->unique(['store_id', 'driver']);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained();
            $table->string('number');               // per-store sequential: 10001...
            $table->string('tracking_token', 40)->unique(); // public tracking link
            $table->string('status')->default('pending');
            // pending, awaiting_payment, payment_review, confirmed, processing, ready, shipped, delivered, cancelled, returned
            $table->string('payment_status')->default('unpaid'); // unpaid, pending_review, paid, refunded, failed
            $table->string('payment_method');
            $table->string('fulfillment')->default('delivery'); // delivery, pickup
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('shipping_zone_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ship_name')->nullable();
            $table->string('ship_phone', 20)->nullable();
            $table->string('ship_state')->nullable();
            $table->string('ship_city')->nullable();
            $table->string('ship_area')->nullable();
            $table->string('ship_address')->nullable();
            $table->string('ship_landmark')->nullable(); // "جنب الجامع" – very common in Sudan
            $table->string('currency', 3)->default('SDG');
            $table->decimal('subtotal', 14, 2);
            $table->decimal('discount', 14, 2)->default(0);
            $table->decimal('shipping_fee', 14, 2)->default(0);
            $table->decimal('payment_fee', 14, 2)->default(0);
            $table->decimal('total', 14, 2);
            $table->decimal('cod_deposit', 14, 2)->default(0); // partial prepayment for COD
            $table->decimal('commission', 14, 2)->default(0);  // platform cut
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['store_id', 'number']);
            $table->index(['store_id', 'status']);
            $table->index(['store_id', 'created_at']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->string('name');
            $table->json('options')->nullable();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 14, 2);
            $table->decimal('unit_cost', 14, 2)->nullable();
            $table->decimal('total', 14, 2);
            $table->timestamps();
        });

        Schema::create('order_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->text('note')->nullable();
            $table->foreignId('user_id')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('driver');
            $table->decimal('amount', 14, 2);
            $table->string('currency', 3)->default('SDG');
            $table->string('status')->default('pending'); // pending, review, approved, rejected, failed
            $table->string('reference')->nullable();       // MBOK transaction id / gateway ref
            $table->string('sender_name')->nullable();
            $table->string('sender_account')->nullable();
            $table->string('receipt_path')->nullable();
            $table->string('receipt_hash', 64)->nullable(); // detect re-used screenshots
            $table->json('gateway_payload')->nullable();
            $table->text('review_note')->nullable();
            $table->foreignId('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['driver', 'reference']);
            $table->index('receipt_hash');
        });

        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shipping_company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('waybill_number')->unique();
            $table->string('tracking_number')->nullable();
            $table->string('driver_name')->nullable();
            $table->string('driver_phone', 20)->nullable();
            $table->decimal('cod_amount', 14, 2)->default(0);
            $table->string('status')->default('created'); // created, picked_up, in_transit, delivered, failed, returned
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['shipments', 'payments', 'order_status_history', 'order_items', 'orders', 'store_payment_methods', 'coupons', 'shipping_zones', 'shipping_companies', 'customers'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
