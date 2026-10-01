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
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('reservation_id', 64)->nullable()->after('order_number')->index();
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->boolean('is_tax_exempt')->default(false)->after('is_active')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex(['reservation_id']);
            $table->dropColumn('reservation_id');
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropIndex(['is_tax_exempt']);
            $table->dropColumn('is_tax_exempt');
        });
    }
};
