<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Enrich order_items with line-item tax and discount tracking
        Schema::table('order_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('order_items', 'allocated_discount')) {
                $table->unsignedBigInteger('allocated_discount')->default(0)->after('discount_amount');
            }
            if (! Schema::hasColumn('order_items', 'is_tax_exempt')) {
                $table->boolean('is_tax_exempt')->default(false)->after('allocated_discount');
            }
            if (! Schema::hasColumn('order_items', 'tax_amount')) {
                $table->unsignedBigInteger('tax_amount')->default(0)->after('is_tax_exempt');
            }
        });

        // 2. Enrich products with Taxpayer System & Return Policy attributes
        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'tax_goods_id')) {
                $table->string('tax_goods_id', 32)->nullable()->after('sku');
            }
            if (! Schema::hasColumn('products', 'is_returnable')) {
                $table->boolean('is_returnable')->default(true)->after('is_tax_exempt');
            }
            if (! Schema::hasColumn('products', 'non_returnable_reason')) {
                $table->string('non_returnable_reason', 255)->nullable()->after('is_returnable');
            }
        });

        // 3. Enrich orders with delivery and Moadian tax UID
        Schema::table('orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('orders', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable()->after('shipped_at');
            }
            if (! Schema::hasColumn('orders', 'moadian_tax_uid')) {
                $table->string('moadian_tax_uid', 32)->nullable()->unique()->after('tracking_url');
            }
            if (! Schema::hasColumn('orders', 'moadian_status')) {
                $table->string('moadian_status', 32)->default('pending')->after('moadian_tax_uid');
            }
        });

        // 4. Double-entry General Ledger (سرفصل‌ها و اسناد حسابداری)
        if (! Schema::hasTable('ledger_accounts')) {
            Schema::create('ledger_accounts', function (Blueprint $table): void {
                $table->id();
                $table->string('code', 32)->unique();
                $table->string('name', 150);
                $table->string('type', 32); // asset, liability, equity, revenue, expense
                $table->string('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ledger_transactions')) {
            Schema::create('ledger_transactions', function (Blueprint $table): void {
                $table->id();
                $table->string('transaction_number', 64)->unique();
                $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
                $table->string('reference_type')->nullable(); // Order, Refund, Wallet, GatewaySettlement
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->string('description', 500);
                $table->timestamp('transacted_at');
                $table->timestamps();

                $table->index(['reference_type', 'reference_id']);
                $table->index('transacted_at');
            });
        }

        if (! Schema::hasTable('ledger_entries')) {
            Schema::create('ledger_entries', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('ledger_transaction_id')->constrained('ledger_transactions')->cascadeOnDelete();
                $table->foreignId('ledger_account_id')->constrained('ledger_accounts')->restrictOnDelete();
                $table->unsignedBigInteger('debit')->default(0); // بدهکار
                $table->unsignedBigInteger('credit')->default(0); // بستانکار
                $table->string('memo')->nullable();
                $table->timestamps();

                $table->index(['ledger_transaction_id', 'ledger_account_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
        Schema::dropIfExists('ledger_transactions');
        Schema::dropIfExists('ledger_accounts');

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn(['delivered_at', 'moadian_tax_uid', 'moadian_status']);
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['tax_goods_id', 'is_returnable', 'non_returnable_reason']);
        });

        Schema::table('order_items', function (Blueprint $table): void {
            $table->dropColumn(['allocated_discount', 'is_tax_exempt', 'tax_amount']);
        });
    }
};
