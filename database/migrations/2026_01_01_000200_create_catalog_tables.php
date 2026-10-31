<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tenant-scoped catalog & inventory tables. Every row carries store_id. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->string('phone', 20)->nullable();
            $table->boolean('allows_pickup')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('image')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
            $table->unique(['store_id', 'slug']);
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->default('simple'); // simple, variable, digital
            $table->string('name');
            $table->string('slug');
            $table->string('sku')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 14, 2);
            $table->decimal('compare_price', 14, 2)->nullable();
            $table->decimal('cost', 14, 2)->nullable();     // for profit reports
            $table->string('price_currency', 3)->default('SDG'); // allows USD-pegged pricing
            $table->boolean('track_stock')->default(true);
            $table->unsignedInteger('low_stock_threshold')->default(3);
            $table->decimal('weight_kg', 8, 3)->nullable();
            $table->string('digital_file')->nullable();
            $table->json('images')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sales_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['store_id', 'slug']);
            $table->index(['store_id', 'is_active']);
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku')->nullable();
            $table->json('options'); // {"المقاس":"L","اللون":"أحمر"}
            $table->decimal('price', 14, 2)->nullable(); // null => product price
            $table->timestamps();
        });

        // Stock per branch; variant_id null => simple product
        Schema::create('stock_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->cascadeOnDelete();
            $table->integer('quantity')->default(0);
            $table->timestamps();
            $table->unique(['branch_id', 'product_id', 'variant_id']);
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stock_level_id')->constrained()->cascadeOnDelete();
            $table->integer('change');
            $table->string('reason'); // order, restock, adjustment, cancel, transfer
            $table->nullableMorphs('reference');
            $table->foreignId('user_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['stock_movements', 'stock_levels', 'product_variants', 'products', 'categories', 'branches'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
