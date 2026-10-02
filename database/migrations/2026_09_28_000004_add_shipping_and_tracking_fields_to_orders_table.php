<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->foreignId('shipping_method_id')
                ->nullable()
                ->after('shipping_method')
                ->constrained('shipping_methods')
                ->nullOnDelete();
            $table->string('tracking_code', 64)->nullable()->index()->after('notes');
            $table->string('tracking_url', 512)->nullable()->after('tracking_code');
            $table->date('delivery_date')->nullable()->after('tracking_url');
            $table->string('delivery_time_slot', 64)->nullable()->after('delivery_date');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropForeign(['shipping_method_id']);
            $table->dropColumn([
                'shipping_method_id',
                'tracking_code',
                'tracking_url',
                'delivery_date',
                'delivery_time_slot',
            ]);
        });
    }
};
