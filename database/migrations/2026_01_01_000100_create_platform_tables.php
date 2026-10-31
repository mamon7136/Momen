<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Platform (landlord) level tables: plans, themes, stores (tenants),
 * domains, staff, subscriptions and exchange rates.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price_monthly', 14, 2)->default(0);
            $table->decimal('price_yearly', 14, 2)->default(0);
            $table->decimal('commission_rate', 5, 2)->default(0); // % on each paid order
            $table->unsignedInteger('max_products')->nullable();   // null = unlimited
            $table->unsignedInteger('max_staff')->nullable();
            $table->unsignedInteger('max_branches')->nullable();
            $table->boolean('custom_domain')->default(false);
            $table->json('features')->nullable();
            $table->unsignedInteger('trial_days')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('preview_image')->nullable();
            $table->json('default_settings')->nullable(); // colors, fonts, sections
            $table->decimal('price', 14, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users');
            $table->foreignId('plan_id')->nullable()->constrained('plans');
            $table->foreignId('theme_id')->nullable()->constrained('themes');
            $table->string('slug')->unique();              // => slug.platform.sd
            $table->string('name');
            $table->string('logo')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('whatsapp', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('currency', 3)->default('SDG');
            $table->string('status')->default('pending'); // pending, active, suspended, expired
            $table->json('theme_settings')->nullable();    // page builder output
            $table->json('settings')->nullable();
            $table->timestamp('subscription_ends_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('store_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('domain')->unique();
            $table->boolean('is_primary')->default(false);
            $table->string('verification_token', 64);
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('store_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('staff'); // owner, manager, staff, accountant, shipping
            $table->json('permissions')->nullable();
            $table->foreignId('branch_id')->nullable();
            $table->timestamps();
            $table->unique(['store_id', 'user_id']);
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained();
            $table->string('cycle')->default('monthly'); // monthly, yearly
            $table->decimal('amount', 14, 2);
            $table->string('status')->default('pending'); // pending, active, expired, cancelled
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->string('receipt_path')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->string('currency', 3)->unique(); // USD, SAR, AED ...
            $table->decimal('rate_to_sdg', 18, 4);
            $table->timestamps();
        });

        Schema::create('apps', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('category'); // payment, shipping, marketing, messaging
            $table->text('description')->nullable();
            $table->decimal('price_monthly', 14, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('store_apps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('app_id')->constrained()->cascadeOnDelete();
            $table->json('config')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
            $table->unique(['store_id', 'app_id']);
        });
    }

    public function down(): void
    {
        foreach (['store_apps', 'apps', 'exchange_rates', 'subscriptions', 'store_staff', 'store_domains', 'stores', 'themes', 'plans'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
