<?php

declare(strict_types=1);

namespace Reyhan\Core\Actions\Tax;

use Reyhan\Core\Models\Order;
use Reyhan\Core\Services\Tax\MoadianTaxService;

final class GenerateMoadianInvoiceAction
{
    public function __construct(
        protected MoadianTaxService $moadianTaxService,
    ) {}

    /**
     * Generate and stamp Moadian Tax UID and export standard payload for an order.
     *
     * @return array{tax_uid: string, payload: array<string, mixed>}
     */
    public function execute(Order $order): array
    {
        if (empty($order->moadian_tax_uid)) {
            $taxUid = $this->moadianTaxService->generateTaxUid($order);
            $order->update([
                'moadian_tax_uid' => $taxUid,
                'moadian_status' => 'generated',
            ]);
        } else {
            $taxUid = $order->moadian_tax_uid;
        }

        $payload = $this->moadianTaxService->buildMoadianPayload($order);

        return [
            'tax_uid' => $taxUid,
            'payload' => $payload,
        ];
    }
}
