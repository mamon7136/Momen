<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Lightweight analytics: unique visits per store per day (one hit per session). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_daily_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->unsignedInteger('visits')->default(0);
            $table->unsignedInteger('product_views')->default(0);
            $table->unique(['store_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_daily_stats');
    }
};
