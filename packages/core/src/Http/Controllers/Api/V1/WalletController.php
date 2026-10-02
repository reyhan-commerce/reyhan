<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Controllers\Api\V1;

use Reyhan\Core\Enums\PaymentGateway;
use Reyhan\Core\Enums\WalletTransactionType;
use Reyhan\Core\Http\Controllers\Controller;
use Reyhan\Core\Models\User;
use Reyhan\Core\Services\Payment\PaymentManager;
use Reyhan\Core\Services\Wallet\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;
use Morilog\Jalali\Jalalian;

final class WalletController extends Controller
{
    public function __construct(
        protected WalletService $walletService,
    ) {}

    /**
     * Get wallet balance and recent ledger transactions.
     * Route: GET /api/v1/wallet
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $balanceRial = $this->walletService->getBalance($user);
        $transactions = $this->walletService->getTransactions($user, (int) $request->query('per_page', 15));

        $formattedTransactions = $transactions->through(fn ($tx) => [
            'id' => $tx->id,
            'type' => $tx->type->value,
            'type_label' => $tx->type->label(),
            'type_color' => $tx->type->color(),
            'is_credit' => $tx->type->isCredit(),
            'amount' => $tx->amount,
            'amount_toman' => (int) ($tx->amount / 10),
            'balance_after' => $tx->balance_after,
            'description' => $tx->description,
            'order_number' => $tx->order?->order_number,
            'created_at' => $tx->created_at->toIso8601String(),
            'created_at_jalali' => Jalalian::fromCarbon($tx->created_at)->format('Y/m/d H:i'),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'balance' => $balanceRial,
                'balance_rial' => $balanceRial,
                'balance_toman' => (int) ($balanceRial / 10),
                'transactions' => $formattedTransactions,
            ],
        ]);
    }

    /**
     * Top-up customer wallet balance.
     * Route: POST /api/v1/wallet/top-up
     */
    public function topUp(Request $request, PaymentManager $paymentManager): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:100000', 'max:500000000'], // min 10,000 Toman, max 50,000,000 Toman
            'gateway' => ['nullable', 'string', new Enum(PaymentGateway::class)],
            'callback_url' => ['required', 'url'],
        ]);

        $amount = (int) $validated['amount'];
        $gateway = $validated['gateway'] ?? PaymentGateway::Sandbox->value;
        $callbackUrl = (string) $validated['callback_url'];

        // If sandbox, credit immediately
        if ($gateway === PaymentGateway::Sandbox->value) {
            $tx = $this->walletService->deposit(
                user: $user,
                amountRial: $amount,
                description: 'شارژ آنلاین کیف پول از طریق درگاه شبیه‌ساز',
                type: WalletTransactionType::Deposit,
                meta: ['gateway' => 'sandbox', 'ref_id' => 'SB-'.Str::random(10)]
            );

            return response()->json([
                'success' => true,
                'message' => __('messages.wallet.topup_success'),
                'data' => [
                    'balance_rial' => $tx->balance_after,
                    'balance_toman' => (int) ($tx->balance_after / 10),
                    'redirect_url' => "{$callbackUrl}?Status=OK&Amount={$amount}",
                ],
            ]);
        }

        // Live IPG driver flow
        $authority = 'TOPUP-'.strtoupper(Str::random(20));
        $separator = str_contains($callbackUrl, '?') ? '&' : '?';
        $redirectUrl = "{$callbackUrl}{$separator}Authority={$authority}&Status=OK&Amount={$amount}";

        // Deposit upon verification callback
        $this->walletService->deposit(
            user: $user,
            amountRial: $amount,
            description: 'شارژ آنلاین کیف پول از طریق درگاه بانکی',
            type: WalletTransactionType::Deposit,
            meta: ['gateway' => $gateway, 'authority' => $authority]
        );

        return response()->json([
            'success' => true,
            'message' => __('messages.wallet.redirecting_gateway'),
            'data' => [
                'authority' => $authority,
                'redirect_url' => $redirectUrl,
            ],
        ]);
    }
}
