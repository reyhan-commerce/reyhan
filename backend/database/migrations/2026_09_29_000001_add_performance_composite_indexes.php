<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table): void {
            $table->index(['product_id', 'is_active', 'price'], 'idx_product_variants_lookup');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->index(['user_id', 'status', 'created_at'], 'idx_orders_user_status_date');
        });

        Schema::table('cart_items', function (Blueprint $table): void {
            $table->index(['cart_id', 'product_variant_id'], 'idx_cart_items_lookup');
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->index(['order_id', 'status'], 'idx_payments_order_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table): void {
            $table->dropIndex('idx_product_variants_lookup');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex('idx_orders_user_status_date');
        });

        Schema::table('cart_items', function (Blueprint $table): void {
            $table->dropIndex('idx_cart_items_lookup');
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->dropIndex('idx_payments_order_status');
        });
    }
};
