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
        Schema::create('specification_groups', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });

        Schema::create('specifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('specification_group_id')->constrained('specification_groups')->cascadeOnDelete();
            $table->string('name');
            $table->string('unit')->nullable();
            $table->string('type')->default('text');
            $table->jsonb('options')->nullable();
            $table->boolean('is_filterable')->default(false);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });

        Schema::create('category_specifications', function (Blueprint $table): void {
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('specification_id')->constrained('specifications')->cascadeOnDelete();
            $table->primary(['category_id', 'specification_id']);
        });

        Schema::create('product_specifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('specification_id')->constrained('specifications')->cascadeOnDelete();
            $table->text('value');
            $table->timestamps();

            $table->unique(['product_id', 'specification_id']);
            $table->index(['specification_id', 'value']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_specifications');
        Schema::dropIfExists('category_specifications');
        Schema::dropIfExists('specifications');
        Schema::dropIfExists('specification_groups');
    }
};
