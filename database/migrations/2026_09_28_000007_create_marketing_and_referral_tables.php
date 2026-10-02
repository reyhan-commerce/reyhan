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
        // 1. Add referral fields to users
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'referral_code')) {
                $table->string('referral_code', 32)->nullable()->unique()->after('wallet_balance');
            }
            if (! Schema::hasColumn('users', 'referred_by')) {
                $table->foreignId('referred_by')->nullable()->after('referral_code')->constrained('users')->nullOnDelete();
            }
        });

        // 2. Banners & Sliders CMS
        Schema::create('banners', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('image_url', 512);
            $table->string('mobile_image_url', 512)->nullable();
            $table->string('link_url', 512)->nullable();
            $table->string('position', 32)->default('home_slider')->index();
            $table->integer('order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });

        // 3. Customer Referral Program
        Schema::create('referrals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referred_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('status', 32)->default('pending')->index();
            $table->unsignedBigInteger('reward_amount')->default(0);
            $table->string('reward_type', 32)->default('wallet');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // 4. Abandoned Cart Recovery Logs
        Schema::create('abandoned_cart_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cart_id')->constrained('carts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('notified_at')->useCurrent();
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
            $table->string('status', 32)->default('sent')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('abandoned_cart_logs');
        Schema::dropIfExists('referrals');
        Schema::dropIfExists('banners');

        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'referred_by')) {
                $table->dropConstrainedForeignId('referred_by');
            }
            if (Schema::hasColumn('users', 'referral_code')) {
                $table->dropColumn('referral_code');
            }
        });
    }
};
