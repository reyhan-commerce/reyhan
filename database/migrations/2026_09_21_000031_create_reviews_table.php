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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating')->default(5);
            $table->unsignedTinyInteger('longevity_rating')->default(5);
            $table->unsignedTinyInteger('coverage_rating')->default(5);
            $table->unsignedTinyInteger('value_rating')->default(5);
            $table->text('comment');
            $table->jsonb('strengths')->nullable();
            $table->jsonb('weaknesses')->nullable();
            $table->boolean('is_verified_purchase')->default(false);
            $table->string('status')->default('approved');
            $table->text('admin_reply')->nullable();
            $table->timestamp('admin_reply_at')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'status']);
            $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
