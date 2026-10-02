<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add wallet_balance to users table
        Schema::table('users', function (Blueprint $table): void {
            $table->unsignedBigInteger('wallet_balance')->default(0)->after('mobile_verified_at');
        });

        // 2. Create wallet_transactions ledger table
        Schema::create('wallet_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('type', 32)->index(); // deposit, withdraw, refund, cashback, admin_adjustment
            $table->unsignedBigInteger('amount'); // in Rial
            $table->unsignedBigInteger('balance_after'); // in Rial
            $table->string('description');
            $table->jsonb('meta')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });

        // 3. Add corporate tax invoice fields & wallet paid amount to orders table
        Schema::table('orders', function (Blueprint $table): void {
            $table->unsignedBigInteger('wallet_paid_amount')->default(0)->after('shipping_fee');
            $table->boolean('is_corporate_invoice')->default(false)->after('notes');
            $table->jsonb('corporate_data')->nullable()->after('is_corporate_invoice');
        });

        // 4. Create card_transfer_receipts table for offline card-to-card payments
        Schema::create('card_transfer_receipts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->unsignedBigInteger('amount'); // in Rial
            $table->string('tracking_number', 64)->index();
            $table->string('source_card_number', 32)->nullable();
            $table->string('destination_card_number', 32)->nullable();
            $table->timestamp('transferred_at')->nullable();
            $table->string('receipt_path', 512)->nullable();
            $table->string('status', 32)->default('pending')->index(); // pending, approved, rejected
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_transfer_receipts');

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn(['wallet_paid_amount', 'is_corporate_invoice', 'corporate_data']);
        });

        Schema::dropIfExists('wallet_transactions');

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('wallet_balance');
        });
    }
};
