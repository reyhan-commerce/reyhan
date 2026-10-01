<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->integer('points'); // positive = earn, negative = spend
            $table->string('type', 50)->index(); // signup_bonus, order_reward, review_bonus, coupon_redemption, manual_adjustment
            $table->string('description');
            $table->string('reference_id')->nullable()->index(); // order_number, coupon_code, etc.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_transactions');
    }
};
