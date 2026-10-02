<?php

declare(strict_types=1);

namespace Reyhan\Core\Database\Factories;

use Reyhan\Core\Enums\PaymentGateway;
use Reyhan\Core\Enums\PaymentStatus;
use Reyhan\Core\Models\Order;
use Reyhan\Core\Models\Payment;
use Reyhan\Core\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'user_id' => User::factory(),
            'gateway' => PaymentGateway::Sandbox,
            'status' => PaymentStatus::Pending,
            'amount' => 500_000,
            'authority' => 'AUTH_'.fake()->unique()->bothify('########'),
            'reference_id' => null,
            'tracking_code' => Payment::generateTrackingCode(),
            'card_pan' => null,
            'gateway_response' => null,
            'paid_at' => null,
        ];
    }

    public function success(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::Success,
            'reference_id' => 'REF-'.fake()->unique()->numerify('######'),
            'card_pan' => '603799******1234',
            'gateway_response' => ['status' => 100, 'message' => 'تراکنش با موفقیت انجام شد.'],
            'paid_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::Failed,
            'gateway_response' => ['status' => -11, 'message' => 'تراکنش ناموفق بود.'],
        ]);
    }
}
