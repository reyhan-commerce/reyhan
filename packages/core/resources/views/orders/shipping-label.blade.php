<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>برچسب مرسوله پستی - {{ $order->order_number }}</title>
    <style>
        @page {
            size: 100mm 150mm;
            margin: 0;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            font-family: Tahoma, 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 4mm;
            width: 100mm;
            max-width: 100mm;
            height: 150mm;
            font-size: 10px;
            color: #000;
            background: #fff;
            line-height: 1.35;
        }
        .label-container {
            border: 2px solid #000;
            border-radius: 4px;
            height: 142mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 3mm;
        }
        .header {
            border-bottom: 2px solid #000;
            padding-bottom: 2mm;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .store-title {
            font-size: 13px;
            font-weight: 900;
        }
        .shipping-badge {
            border: 1.5px solid #000;
            padding: 1px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        .order-meta {
            text-align: left;
            font-size: 9px;
            font-family: monospace;
            font-weight: bold;
        }
        .barcode-box {
            text-align: center;
            margin: 2mm 0;
            padding: 2mm 0;
            border-bottom: 1px dashed #000;
        }
        .barcode-lines {
            display: inline-block;
            height: 12mm;
            letter-spacing: 2px;
            font-family: monospace;
            font-size: 16px;
            font-weight: 900;
        }
        .section {
            border-bottom: 1.5px solid #000;
            padding: 2mm 0;
        }
        .section-title {
            font-size: 9px;
            font-weight: 900;
            background: #000;
            color: #fff;
            display: inline-block;
            padding: 1px 4px;
            border-radius: 2px;
            margin-bottom: 1.5mm;
        }
        .recipient-box {
            background: #f8f8f8;
            border: 1px solid #000;
            padding: 2.5mm;
            border-radius: 3px;
        }
        .postal-code {
            font-size: 13px;
            font-weight: 900;
            letter-spacing: 2px;
            font-family: monospace;
        }
        .bold-text {
            font-weight: 900;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
            margin-top: 1.5mm;
        }
        .items-table th, .items-table td {
            border: 1px solid #333;
            padding: 1.5px 3px;
            text-align: right;
        }
        .items-table th {
            background: #eee;
            font-weight: bold;
        }
        .footer-note {
            font-size: 8px;
            text-align: center;
            margin-top: 1mm;
            border-top: 1px dashed #666;
            padding-top: 1mm;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 10px; text-align: center;">
        <button onclick="window.print()" style="padding: 6px 16px; font-weight: bold; cursor: pointer; background: #000; color: #fff; border: none; border-radius: 4px;">
            🖨️ چاپ برچسب مرسوله حرارتی (100x150mm)
        </button>
    </div>

    @php
        $address = $order->shipping_address ?? [];
        $recipientName = $address['recipient_name'] ?? ($order->user?->full_name ?? 'مشتری محترم');
        $recipientMobile = $address['recipient_mobile'] ?? ($order->user?->mobile ?? '-');
        $province = $address['province_name'] ?? '-';
        $city = $address['city_name'] ?? '-';
        $fullAddress = $address['full_address'] ?? ($address['address_line'] ?? '-');
        $postalCode = $address['postal_code'] ?? '----------';
        $shippingTitle = $order->shippingMethod?->name ?? ($order->shipping_method instanceof \Reyhan\Core\Enums\ShippingMethod ? $order->shipping_method->title() : ($order->shipping_method ?? 'پست پیشتاز'));
    @endphp

    <div class="label-container">
        <!-- Header -->
        <div>
            <div class="header">
                <div>
                    <span class="store-title">{{ $settings->site_name ?? 'فروشگاه اینترنتی' }}</span>
                </div>
                <div class="shipping-badge">
                    {{ $shippingTitle }}
                </div>
                <div class="order-meta">
                    {{ $order->order_number }}
                </div>
            </div>

            <!-- Barcode Placeholder -->
            <div class="barcode-box">
                <div class="barcode-lines">||| | |||| | ||||| || ||||</div>
                <div style="font-family: monospace; font-size: 9px; font-weight: bold;">
                    *{{ $order->order_number }}*
                </div>
            </div>

            <!-- Sender Info -->
            <div class="section" style="padding-top: 1mm; padding-bottom: 1.5mm;">
                <span class="section-title">فرستنده:</span>
                <span style="font-size: 8.5px;">
                    {{ $settings->site_name ?? 'فروشگاه مرکزی' }} - تلفن پشتیبانی: {{ $settings->support_phone ?? '۰۲۱-۹۱۰۰۰۰۰۰' }}
                </span>
            </div>

            <!-- Recipient Info -->
            <div class="section recipient-box">
                <div style="display: flex; justify-content: space-between; align-items: baseline;">
                    <div>
                        <span class="section-title">گیرنده:</span>
                        <span class="bold-text" style="font-size: 11px;">{{ $recipientName }}</span>
                    </div>
                    <div>
                        <span class="bold-text">تلفن:</span>
                        <span style="font-family: monospace; font-size: 10px; font-weight: bold;">{{ $recipientMobile }}</span>
                    </div>
                </div>

                <div style="margin-top: 1.5mm;">
                    <span class="bold-text">مقصد:</span>
                    <span>استان {{ $province }}، شهر {{ $city }}</span>
                </div>

                <div style="margin-top: 1mm;">
                    <span class="bold-text">نشانی کامل:</span>
                    <span style="font-size: 9.5px;">{{ $fullAddress }}</span>
                </div>

                <div style="margin-top: 2mm; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #ccc; padding-top: 1.5mm;">
                    <div>
                        <span class="bold-text">کد پستی ۱۰ رقمی:</span>
                        <span class="postal-code">{{ chunk_split($postalCode, 5, ' ') }}</span>
                    </div>
                    @if($order->delivery_time_slot)
                        <div style="font-size: 8.5px; font-weight: bold; background: #000; color: #fff; padding: 1px 4px; border-radius: 2px;">
                            {{ $order->delivery_time_slot }}
                        </div>
                    @endif
                </div>
            </div>

            @if($order->tracking_code)
                <div style="margin-top: 1.5mm; font-size: 9px; font-weight: bold;">
                    کد رهگیری مرسوله: <span style="font-family: monospace; letter-spacing: 1px;">{{ $order->tracking_code }}</span>
                </div>
            @endif

            <!-- Items Table Preview -->
            <div style="margin-top: 2mm;">
                <div style="font-size: 8.5px; font-weight: bold;">محتوای بسته (تعداد کل: {{ $order->items->sum('quantity') }} عدد):</div>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 60%;">شرح کالا</th>
                            <th style="width: 25%;">تنوع</th>
                            <th style="width: 15%; text-align: center;">تعداد</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items->take(5) as $item)
                            <tr>
                                <td>{{ \Illuminate\Support\Str::limit($item->product_name, 28) }}</td>
                                <td>{{ $item->variant_title ?? '-' }}</td>
                                <td style="text-align: center; font-weight: bold;">{{ $item->quantity }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer-note">
            تحویل توسط شرکت پست / پیک طرف قرارداد • در حفظ و نگهداری مرسوله کوشا باشید.
        </div>
    </div>
</body>
</html>
