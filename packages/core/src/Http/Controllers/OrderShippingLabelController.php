<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Order;
use App\Settings\GeneralSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\URL;

final class OrderShippingLabelController extends Controller
{
    /**
     * Display printable 100x150mm thermal shipping label.
     */
    public function show(Order $order, GeneralSettings $settings): View
    {
        $order->load(['items.product', 'items.productVariant', 'user', 'shippingMethod']);

        return view('orders.shipping-label', [
            'order' => $order,
            'settings' => $settings,
        ]);
    }

    /**
     * Generate a temporary signed URL for viewing the shipping label.
     */
    public static function generateLabelUrl(Order $order): string
    {
        return URL::temporarySignedRoute(
            'orders.shipping-label',
            now()->addHours(4),
            ['order' => $order->id]
        );
    }
}
