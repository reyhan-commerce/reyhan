<?php

declare(strict_types=1);

namespace Reyhan\Core\Actions\Catalog;

use Reyhan\Core\Models\ProductVariant;
use Reyhan\Core\Models\StockAlert;
use Reyhan\Core\Models\User;
use Reyhan\Core\Pipelines\Normalizer\PersianNormalizer;

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
