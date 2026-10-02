<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('title')->nullable();
            $table->string('type')->default('percentage');
            $table->unsignedInteger('value');
            $table->unsignedBigInteger('min_order_amount')->nullable();
            $table->unsignedBigInteger('max_discount_amount')->nullable();
            $table->string('scope')->default('all');
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->unsignedSmallInteger('usage_limit_per_user')->default(1);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['code', 'is_active']);
        });

        Schema::create('coupon_usages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('coupon_id')->constrained('coupons')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('discount_amount');
            $table->timestamps();

            $table->index(['coupon_id', 'user_id']);
        });

        Schema::create('coupon_categories', function (Blueprint $table): void {
            $table->foreignId('coupon_id')->constrained('coupons')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->primary(['coupon_id', 'category_id']);
        });

        Schema::create('coupon_brands', function (Blueprint $table): void {
            $table->foreignId('coupon_id')->constrained('coupons')->cascadeOnDelete();
            $table->foreignId('brand_id')->constrained('brands')->cascadeOnDelete();
            $table->primary(['coupon_id', 'brand_id']);
        });

        Schema::create('coupon_variants', function (Blueprint $table): void {
            $table->foreignId('coupon_id')->constrained('coupons')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->primary(['coupon_id', 'product_variant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_variants');
        Schema::dropIfExists('coupon_brands');
        Schema::dropIfExists('coupon_categories');
        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('coupons');
    }
};
