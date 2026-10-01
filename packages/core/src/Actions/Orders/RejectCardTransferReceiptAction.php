<?php

declare(strict_types=1);

namespace App\Actions\Orders;

use App\Models\CardTransferReceipt;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

final class RejectCardTransferReceiptAction
{
    /**
     * Reject offline card transfer receipt with reason.
     */
    public function execute(Order $order, CardTransferReceipt $receipt, ?int $adminId = null, ?string $adminNotes = null): void
    {
        DB::transaction(function () use ($receipt, $adminId, $adminNotes): void {
            $receipt->update([
                'status' => 'rejected',
                'reviewed_by' => $adminId ?? auth()->id(),
                'reviewed_at' => now(),
                'admin_notes' => $adminNotes,
            ]);
        });
    }
}
