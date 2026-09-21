<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('gateway', 32)->default('sandbox');
            $table->string('status', 32)->default('pending')->index();
            $table->unsignedBigInteger('amount');
            $table->string('authority', 128)->unique();
            $table->string('reference_id', 128)->nullable();
            $table->string('tracking_code', 64)->nullable();
            $table->string('card_pan', 32)->nullable();
            $table->jsonb('gateway_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['order_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
