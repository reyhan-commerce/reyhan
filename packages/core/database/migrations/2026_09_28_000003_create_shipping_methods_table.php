<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_methods', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->unsignedBigInteger('base_cost')->default(0); // In Rial
            $table->unsignedBigInteger('cost_per_kg')->default(0); // In Rial
            $table->unsignedBigInteger('free_shipping_threshold')->nullable(); // In Rial; null inherits store setting
            $table->string('estimated_delivery_days')->nullable();
            $table->boolean('requires_time_slot')->default(false);
            $table->jsonb('supported_provinces')->nullable(); // Array of province IDs, null = nationwide
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_methods');
    }
};
