<?php

declare(strict_types=1);

namespace Reyhan\Core\Database\Factories;

use Reyhan\Core\Enums\OrderStatus;
use Reyhan\Core\Enums\ShippingMethod;
use Reyhan\Core\Models\Order;
use Reyhan\Core\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(200_000, 3_000_000);
        $shippingFee = 45_000;
        $discount = 0;
        $finalPayable = $subtotal + $shippingFee - $discount;

        return [
            'order_number' => Order::generateOrderNumber(),
            'user_id' => User::factory(),
            'status' => OrderStatus::PendingPayment,
            'shipping_method' => ShippingMethod::Pishtaz,
            'shipping_address' => [
                'recipient_name' => fake()->name(),
                'recipient_mobile' => '09'.fake()->numerify('#########'),
                'province' => 'تهران',
                'city' => 'تهران',
                'address_line' => 'خیابان ولیعصر، کوچه بهار، پلاک ۱۰',
                'postal_code' => fake()->numerify('##########'),
            ],
            'items_subtotal' => $subtotal,
            'discount_amount' => $discount,
            'coupon_discount' => 0,
            'coupon_code' => null,
            'shipping_fee' => $shippingFee,
            'final_payable' => $finalPayable,
            'notes' => null,
            'paid_at' => null,
            'shipped_at' => null,
            'cancelled_at' => null,
        ];
    }

    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Processing,
            'paid_at' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Delivered,
            'paid_at' => now()->subDays(3),
            'shipped_at' => now()->subDays(2),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Cancelled,
            'cancelled_at' => now(),
        ]);
    }
}
