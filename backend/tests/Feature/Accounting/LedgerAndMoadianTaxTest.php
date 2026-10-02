<?php

declare(strict_types=1);

use Reyhan\Core\Actions\Accounting\CreateLedgerJournalEntryAction;
use Reyhan\Core\Actions\Tax\GenerateMoadianInvoiceAction;
use Reyhan\Core\Database\Seeders\LedgerAccountsSeeder;
use Reyhan\Core\Enums\CouponType;
use Reyhan\Core\Enums\PaymentGateway;
use Reyhan\Core\Enums\PaymentStatus;
use Reyhan\Core\Models\Address;
use Reyhan\Core\Models\Cart;
use Reyhan\Core\Models\CartItem;
use Reyhan\Core\Models\City;
use Reyhan\Core\Models\Coupon;
use Reyhan\Core\Models\LedgerEntry;
use Reyhan\Core\Models\LedgerTransaction;
use Reyhan\Core\Models\Order;
use Reyhan\Core\Models\OrderItem;
use Reyhan\Core\Models\Payment;
use Reyhan\Core\Models\Product;
use Reyhan\Core\Models\ProductVariant;
use Reyhan\Core\Models\Province;
use Reyhan\Core\Models\ShippingMethod;
use Reyhan\Core\Models\User;

beforeEach(function (): void {
    $this->seed(LedgerAccountsSeeder::class);

    $this->user = User::factory()->create([
        'mobile' => '09123456789',
        'wallet_balance' => 5000000,
    ]);

    $this->province = Province::firstOrCreate(['id' => 1], ['name' => 'تهران', 'slug' => 'tehran']);
    $this->city = City::firstOrCreate(['id' => 1], ['province_id' => $this->province->id, 'name' => 'تهران', 'slug' => 'tehran-city']);

    $this->address = Address::factory()->create([
        'user_id' => $this->user->id,
        'province_id' => $this->province->id,
        'city_id' => $this->city->id,
        'postal_code' => '1998835111',
    ]);

    $this->product = Product::factory()->create([
        'is_tax_exempt' => false,
        'tax_goods_id' => '2720000143894',
    ]);

    $this->variant = ProductVariant::factory()->create([
        'product_id' => $this->product->id,
        'price' => 1000000,
        'compare_at_price' => 1200000,
        'stock' => 50,
    ]);

    $this->shippingMethod = ShippingMethod::factory()->create([
        'slug' => 'pishtaz',
        'base_cost' => 650000,
        'is_active' => true,
    ]);
});

it('records double-entry balanced journal entries for order settlement', function (): void {
    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'items_subtotal' => 1000000,
        'discount_amount' => 200000,
        'coupon_discount' => 100000,
        'shipping_fee' => 650000,
        'tax_amount' => 90000,
        'wallet_paid_amount' => 300000,
        'final_payable' => 1640000,
    ]);

    $payment = Payment::create([
        'order_id' => $order->id,
        'user_id' => $this->user->id,
        'gateway' => PaymentGateway::Sandbox,
        'status' => PaymentStatus::Success,
        'amount' => 1340000, // 1640000 - 300000 wallet
        'authority' => 'TEST-AUTH-123',
        'tracking_code' => 'TRK-998877',
        'paid_at' => now(),
    ]);

    $action = app(CreateLedgerJournalEntryAction::class);
    $tx = $action->recordOrderSettlement($order, $payment);

    expect($tx)->toBeInstanceOf(LedgerTransaction::class)
        ->and($tx->order_id)->toBe($order->id);

    $entries = LedgerEntry::where('ledger_transaction_id', $tx->id)->get();
    $totalDebit = $entries->sum('debit');
    $totalCredit = $entries->sum('credit');

    // Strict Double-Entry Balance Check (بدهکار = بستانکار)
    expect($totalDebit)->toBe($totalCredit)
        ->and($totalDebit)->toBe(1740000); // 1340000 bank + 300000 wallet + 100000 coupon discount
});

it('generates electronic Moadian tax UID and exports compliant payload', function (): void {
    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'items_subtotal' => 1000000,
        'discount_amount' => 200000,
        'coupon_discount' => 0,
        'shipping_fee' => 650000,
        'tax_amount' => 100000,
        'final_payable' => 1750000,
        'is_corporate_invoice' => true,
        'corporate_data' => [
            'company_name' => 'شرکت توسعه پایدار فناوران',
            'national_id' => '10861676731',
            'economic_code' => '411485297531',
        ],
    ]);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $this->product->id,
        'product_variant_id' => $this->variant->id,
        'quantity' => 1,
        'unit_price' => 1200000,
        'discount_amount' => 200000,
        'final_price' => 1000000,
        'allocated_discount' => 0,
        'is_tax_exempt' => false,
        'tax_amount' => 100000,
        'total_price' => 1000000,
    ]);

    $action = app(GenerateMoadianInvoiceAction::class);
    $result = $action->execute($order);

    expect($result['tax_uid'])->toHaveLength(22)
        ->and($order->fresh()->moadian_tax_uid)->toBe($result['tax_uid'])
        ->and($result['payload']['header']['inty'])->toBe(1) // B2B الگوی اول
        ->and($result['payload']['header']['tinb'])->toBe('10861676731')
        ->and($result['payload']['body'][0]['sstid'])->toBe('2720000143894')
        ->and($result['payload']['body'][0]['vam'])->toBe(100000);
});

it('allocates coupon discounts pro-rata and records line-item VAT during checkout', function (): void {
    $cart = Cart::factory()->create(['user_id' => $this->user->id]);

    $coupon = Coupon::factory()->create([
        'code' => 'PROMO100',
        'type' => CouponType::Fixed,
        'value' => 100000, // 100,000 Rial discount
        'min_order_amount' => 500000,
        'is_active' => true,
    ]);
    $cart->update(['coupon_id' => $coupon->id]);

    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_variant_id' => $this->variant->id,
        'quantity' => 2,
    ]);

    $shippingMethod = ShippingMethod::where('slug', 'pishtaz')->first() ?? ShippingMethod::first();

    $response = $this->actingAs($this->user)
        ->postJson('/api/v1/checkout/create-order', [
            'address_id' => $this->address->id,
            'shipping_method_id' => $shippingMethod->id,
            'gateway' => PaymentGateway::Sandbox->value,
            'callback_url' => 'http://localhost:3000/checkout/callback',
        ]);

    $response->assertStatus(201);
    $orderNumber = $response->json('data.order_number');
    $order = Order::where('order_number', $orderNumber)->first();

    expect($order)->not->toBeNull();
    $item = $order->items->first();

    expect($item)->not->toBeNull()
        ->and($item->allocated_discount)->toBe(100000)
        ->and($item->is_tax_exempt)->toBeFalse()
        ->and($item->tax_amount)->toBeGreaterThan(0);
});
