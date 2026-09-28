<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Enums\TicketDepartment;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductQuestion;
use App\Models\ProductVariant;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    $this->user = User::factory()->create([
        'mobile' => '09121112233',
        'is_active' => true,
        'wallet_balance' => 0,
    ]);

    $category = Category::factory()->create();
    $brand = Brand::factory()->create();

    $this->product = Product::factory()->create([
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'is_active' => true,
    ]);

    $this->variant = ProductVariant::factory()->create([
        'product_id' => $this->product->id,
        'stock' => 10,
        'price' => 2000000,
        'is_active' => true,
    ]);
});

test('user can submit 7-day return request for delivered order and list returns', function (): void {
    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'status' => OrderStatus::Delivered,
        'final_payable' => 4000000,
        'shipped_at' => now()->subDays(2),
    ]);

    $orderItem = OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $this->product->id,
        'product_variant_id' => $this->variant->id,
        'product_name' => $this->product->name,
        'variant_title' => 'مشکی',
        'sku' => 'SKU-TEST-123',
        'unit_price' => 2000000,
        'quantity' => 2,
        'total_price' => 4000000,
        'final_price' => 4000000,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson("/api/v1/orders/{$order->order_number}/returns", [
            'reason' => 'عدم تطابق رنگ با تصاویر سایت',
            'description' => 'کالای ارسالی رنگ دیگری بود.',
            'items' => [
                [
                    'order_item_id' => $orderItem->id,
                    'quantity' => 1,
                    'reason' => 'رنگ نامطابق',
                ],
            ],
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.status', 'pending');

    $returnNumber = $response->json('data.return_number');

    $listResponse = $this->actingAs($this->user)
        ->getJson('/api/v1/profile/returns');

    $listResponse->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.data.0.return_number', $returnNumber);

    $detailResponse = $this->actingAs($this->user)
        ->getJson("/api/v1/profile/returns/{$returnNumber}");

    $detailResponse->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.return_number', $returnNumber)
        ->assertJsonCount(1, 'data.items');
});

test('product questions and answers workflow with moderation and likes', function (): void {
    // 1. Submit question using slug
    $postResponse = $this->actingAs($this->user)
        ->postJson("/api/v1/products/{$this->product->slug}/questions", [
            'question' => 'آیا این کالا دارای گارانتی معتبر شرکتی است؟',
        ]);

    $postResponse->assertStatus(201)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.is_approved', false);

    $questionId = $postResponse->json('data.id');

    // 2. Public index does not show unapproved question
    $publicResponse = $this->getJson("/api/v1/products/{$this->product->slug}/questions");
    $publicResponse->assertOk()
        ->assertJsonCount(0, 'data.data');

    // 3. Approve question and add answer
    $question = ProductQuestion::findOrFail($questionId);
    $question->update(['is_approved' => true]);

    $answerResponse = $this->actingAs($this->user)
        ->postJson("/api/v1/questions/{$question->id}/answers", [
            'answer' => 'بله گارانتی ۱۸ ماهه اصلی دارد.',
        ]);

    $answerResponse->assertStatus(201);
    $question->answers()->first()->update(['is_approved' => true]);

    // 4. Public index now displays question and answer
    $publicApproved = $this->getJson("/api/v1/products/{$this->product->slug}/questions");
    $publicApproved->assertOk()
        ->assertJsonCount(1, 'data.data')
        ->assertJsonCount(1, 'data.data.0.answers');

    // 5. Like question
    $likeResponse = $this->postJson("/api/v1/questions/{$question->id}/like");
    $likeResponse->assertOk()
        ->assertJsonPath('data.likes_count', 1);
});

test('support ticket lifecycle create message and close', function (): void {
    // 1. Create ticket
    $createResponse = $this->actingAs($this->user)
        ->postJson('/api/v1/tickets', [
            'subject' => 'درخواست تغییر آدرس ارسال سفارش',
            'department' => TicketDepartment::Shipping->value,
            'priority' => TicketPriority::High->value,
            'message' => 'لطفاً مرسوله را به آدرس جدید ارسال فرمایید.',
        ]);

    $createResponse->assertStatus(201)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.status', 'open');

    $ticketNumber = $createResponse->json('data.ticket_number');

    // 2. View ticket detail
    $showResponse = $this->actingAs($this->user)
        ->getJson("/api/v1/tickets/{$ticketNumber}");

    $showResponse->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(1, 'data.messages');

    // 3. Reply to ticket
    $replyResponse = $this->actingAs($this->user)
        ->postJson("/api/v1/tickets/{$ticketNumber}/messages", [
            'message' => 'آدرس جدید در سامانه ثبت گردید.',
        ]);

    $replyResponse->assertStatus(201)
        ->assertJsonPath('success', true);

    // 4. Close ticket
    $closeResponse = $this->actingAs($this->user)
        ->putJson("/api/v1/tickets/{$ticketNumber}/close");

    $closeResponse->assertOk()
        ->assertJsonPath('success', true);

    $ticket = SupportTicket::where('ticket_number', $ticketNumber)->first();
    expect($ticket->status)->toBe(TicketStatus::Closed);
});

test('product price history tracks changes and provides visual chart points', function (): void {
    // 1. Initial price history was created on variant creation in beforeEach
    expect($this->product->priceHistories()->count())->toBeGreaterThan(0);

    // 2. Change price
    $this->variant->update(['price' => 2500000]);

    expect($this->product->priceHistories()->count())->toBe(2);

    // 3. API endpoint returns chart points and stats using slug
    $response = $this->getJson("/api/v1/products/{$this->product->slug}/price-history");

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.min_price', 2000000)
        ->assertJsonPath('data.max_price', 2500000)
        ->assertJsonCount(2, 'data.points');
});
