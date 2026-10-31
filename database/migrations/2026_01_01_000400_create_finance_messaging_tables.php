<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Wallets (double-entry style ledger), settlements, messaging log, OTP. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('balance', 16, 2)->default(0);   // withdrawable
            $table->decimal('pending', 16, 2)->default(0);   // COD not yet collected from courier
            $table->string('currency', 3)->default('SDG');
            $table->timestamps();
        });

        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // sale, commission, payout, refund, adjustment, subscription
            $table->decimal('amount', 16, 2); // signed
            $table->decimal('balance_after', 16, 2);
            $table->nullableMorphs('reference');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 16, 2);
            $table->string('method')->default('bank_transfer'); // bank_transfer, mbok
            $table->string('account_details');
            $table->string('status')->default('requested'); // requested, paid, rejected
            $table->string('reference')->nullable();
            $table->foreignId('processed_by')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('message_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('channel'); // whatsapp, sms
            $table->string('to', 20);
            $table->string('template')->nullable();
            $table->text('body');
            $table->string('status')->default('queued'); // queued, sent, failed
            $table->string('provider_id')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });

        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20)->index();
            $table->string('code_hash');
            $table->string('purpose')->default('login');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamps();
        });

        Schema::create('abandoned_carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('phone', 20)->nullable();
            $table->json('items');
            $table->decimal('total', 14, 2);
            $table->timestamp('reminded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['abandoned_carts', 'otp_codes', 'message_logs', 'payouts', 'wallet_transactions', 'wallets'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
