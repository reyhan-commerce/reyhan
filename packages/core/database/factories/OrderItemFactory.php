<?php

declare(strict_types=1);

namespace Reyhan\Core\Database\Factories;

use Reyhan\Core\Models\Order;
use Reyhan\Core\Models\OrderItem;
use Reyhan\Core\Models\Product;
use Reyhan\Core\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $unitPrice = 250_000;
        $quantity = 1;
        $discountAmount = 0;
        $finalPrice = $unitPrice - $discountAmount;
        $totalPrice = $finalPrice * $quantity;

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_variant_id' => ProductVariant::factory(),
            'product_name' => 'سرم ویتامین C روشن‌کننده',
            'variant_title' => 'حجم ۵۰ میلی‌لیتر',
            'sku' => 'SKU-'.fake()->unique()->numerify('#####'),
            'unit_price' => $unitPrice,
            'discount_amount' => $discountAmount,
            'final_price' => $finalPrice,
            'quantity' => $quantity,
            'total_price' => $totalPrice,
            'attributes_snapshot' => [
                'حجم' => '۵۰ میلی‌لیتر',
            ],
        ];
    }
}
