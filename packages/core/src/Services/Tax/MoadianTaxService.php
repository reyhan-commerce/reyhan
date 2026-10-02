<?php

declare(strict_types=1);

namespace Reyhan\Core\Services\Tax;

use Reyhan\Core\Models\Order;
use Carbon\Carbon;

final class MoadianTaxService
{
    /**
     * Generate standard 22-character unique electronic tax invoice identifier (شماره منحصر به فرد مالیاتی).
     *
     * Format:
     * - 6 chars: Fiscal Memory ID (شناسه یکتای حافظه مالیاتی)
     * - 5 chars: Julian day count since origin
     * - 10 chars: Serial number hex
     * - 1 char: Verhoeff checksum digit
     */
    public function generateTaxUid(Order $order, string $fiscalMemoryId = 'A12345'): string
    {
        $date = $order->paid_at ?? $order->created_at ?? now();
        $daysSinceEpoch = (int) $date->diffInDays(Carbon::create(2020, 1, 1));
        $dayHex = str_pad(dechex($daysSinceEpoch), 5, '0', STR_PAD_LEFT);
        $serialHex = str_pad(dechex($order->id % 10000000), 10, '0', STR_PAD_LEFT);

        $payload = strtoupper($fiscalMemoryId.$dayHex.$serialHex);
        $checkDigit = $this->calculateChecksumDigit($payload);

        return substr($payload.$checkDigit, 0, 22);
    }

    /**
     * Build Iranian standard Moadian electronic invoice payload (الگوی اول B2B / الگوی دوم B2C).
     *
     * @return array<string, mixed>
     */
    public function buildMoadianPayload(Order $order): array
    {
        $order->loadMissing(['items.product', 'items.productVariant', 'user']);

        $isB2B = (bool) $order->is_corporate_invoice;
        $taxUid = $order->moadian_tax_uid ?? $this->generateTaxUid($order);

        $header = [
            'taxid' => $taxUid,
            'indatim' => ($order->paid_at ?? now())->timestamp * 1000,
            'indati2m' => now()->timestamp * 1000,
            'inty' => $isB2B ? 1 : 2, // 1: B2B الگوی اول, 2: B2C الگوی دوم
            'inno' => $order->order_number,
            'irtaxid' => null,
            'inp' => 1, // ۱: فروش
            'ins' => 1, // ۱: صورتحساب اصلی
            'tins' => config('reyhan.store.company_national_id', '10100000000'), // شناسه ملی فروشنده
            'tinb' => $isB2B ? ($order->corporate_data['national_id'] ?? null) : null, // شناسه ملی خریدار
            'sbc' => $isB2B ? ($order->corporate_data['economic_code'] ?? null) : null, // کد اقتصادی خریدار
            'tprdis' => $order->items_subtotal, // مجموع ناخالص
            'tdis' => $order->discount_amount + $order->coupon_discount, // مجموع تخفیفات
            'tadis' => max(0, $order->items_subtotal - ($order->discount_amount + $order->coupon_discount)), // پس از تخفیف
            'tvam' => $order->tax_amount, // مالیات بر ارزش افزوده
            'todam' => 0,
            'tbill' => $order->final_payable, // مجموع نهایی صورتحساب
            'setm' => 1, // ۱: نقدی
            'cap' => $order->final_payable,
            'insp' => 0,
        ];

        $body = [];
        $index = 1;

        foreach ($order->items as $item) {
            $sstid = $item->product?->tax_goods_id ?? '2720000143894'; // شناسه ۱۳ رقمی سرفصل کالا
            $body[] = [
                'sstid' => $sstid,
                'sstt' => $item->product_name,
                'am' => $item->quantity,
                'fee' => $item->unit_price,
                'cfee' => 0,
                'cut' => null,
                'exr' => 0,
                'prdis' => $item->unit_price * $item->quantity,
                'dis' => $item->discount_amount + ($item->allocated_discount ?? 0),
                'adis' => max(0, ($item->unit_price * $item->quantity) - ($item->discount_amount + ($item->allocated_discount ?? 0))),
                'vra' => $item->is_tax_exempt ? 0 : 10,
                'vam' => $item->tax_amount,
                'tsstam' => $item->total_price + $item->tax_amount,
            ];
            $index++;
        }

        return [
            'header' => $header,
            'body' => $body,
            'payments' => [
                [
                    'iinn' => $order->successfulPayment?->tracking_code ?? '000000',
                    'acn' => $order->successfulPayment?->card_pan ?? null,
                    'trmn' => 'SHAPARAK',
                    'trn' => $order->successfulPayment?->reference_id ?? null,
                    'pdt' => ($order->paid_at ?? now())->timestamp * 1000,
                    'pv' => $order->final_payable,
                ],
            ],
        ];
    }

    /**
     * Compute Verhoeff/Luhn variation checksum character for Tax ID.
     */
    private function calculateChecksumDigit(string $input): string
    {
        $sum = 0;
        $len = strlen($input);
        for ($i = 0; $i < $len; $i++) {
            $sum += ord($input[$i]) * ($i + 1);
        }

        return strtoupper(dechex($sum % 16));
    }
}
