<?php

declare(strict_types=1);

namespace App\Actions\Catalog;

use App\Models\ProductVariant;
use App\Models\StockAlert;
use App\Models\User;
use App\Pipelines\Normalizer\PersianNormalizer;

final class CreateStockAlertAction
{
    /**
     * Subscribe user / mobile to be alerted when variant is restocked.
     */
    public function execute(ProductVariant $variant, string $mobile, ?User $user = null): StockAlert
    {
        $cleanMobile = PersianNormalizer::normalizeMobile($mobile);

        return StockAlert::query()->firstOrCreate(
            [
                'product_variant_id' => $variant->id,
                'mobile' => $cleanMobile,
                'status' => 'pending',
            ],
            [
                'user_id' => $user?->id,
            ]
        );
    }
}
