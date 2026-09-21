<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->string('order_number', 32)->unique();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('status', 32)->default('pending_payment')->index();
            $table->string('shipping_method', 32)->default('pishtaz');
            $table->jsonb('shipping_address');
            $table->unsignedBigInteger('items_subtotal');
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('coupon_discount')->default(0);
            $table->string('coupon_code', 64)->nullable();
            $table->unsignedBigInteger('shipping_fee')->default(0);
            $table->unsignedBigInteger('final_payable');
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index('created_at');
        });

        Schema::create('order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants')->restrictOnDelete();
            $table->string('product_name');
            $table->string('variant_title')->nullable();
            $table->string('sku', 64);
            $table->unsignedBigInteger('unit_price');
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('final_price');
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->unsignedBigInteger('total_price');
            $table->jsonb('attributes_snapshot')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'product_variant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
