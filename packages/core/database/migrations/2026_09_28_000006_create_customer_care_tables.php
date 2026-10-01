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
        // 1. Order Returns (RMA)
        Schema::create('order_returns', function (Blueprint $table): void {
            $table->id();
            $table->string('return_number', 32)->unique();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status', 32)->default('pending')->index();
            $table->string('reason', 128);
            $table->text('description')->nullable();
            $table->json('photos')->nullable();
            $table->string('refund_method', 32)->default('wallet');
            $table->unsignedBigInteger('refund_amount')->default(0);
            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        // 2. Order Return Items
        Schema::create('order_return_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_return_id')->constrained('order_returns')->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedBigInteger('price');
            $table->string('reason', 128)->nullable();
            $table->timestamps();
        });

        // 3. Product Questions
        Schema::create('product_questions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('question');
            $table->boolean('is_approved')->default(false)->index();
            $table->unsignedInteger('likes_count')->default(0);
            $table->timestamps();
        });

        // 4. Product Answers
        Schema::create('product_answers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('answer');
            $table->boolean('is_approved')->default(false)->index();
            $table->boolean('is_staff')->default(false);
            $table->unsignedInteger('likes_count')->default(0);
            $table->timestamps();
        });

        // 5. Support Tickets
        Schema::create('support_tickets', function (Blueprint $table): void {
            $table->id();
            $table->string('ticket_number', 32)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('department', 32)->default('support')->index();
            $table->string('priority', 32)->default('medium')->index();
            $table->string('status', 32)->default('open')->index();
            $table->string('subject', 255);
            $table->timestamp('last_reply_at')->nullable();
            $table->timestamps();
        });

        // 6. Support Ticket Messages
        Schema::create('support_ticket_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ticket_id')->constrained('support_tickets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('message');
            $table->boolean('is_staff')->default(false);
            $table->json('attachments')->nullable();
            $table->timestamps();
        });

        // 7. Product Price Histories
        Schema::create('product_price_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('price');
            $table->timestamp('recorded_at')->useCurrent()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_price_histories');
        Schema::dropIfExists('support_ticket_messages');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('product_answers');
        Schema::dropIfExists('product_questions');
        Schema::dropIfExists('order_return_items');
        Schema::dropIfExists('order_returns');
    }
};
