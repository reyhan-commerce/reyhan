<?php

declare(strict_types=1);

namespace Reyhan\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

final class MakePaymentDriverCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reyhan:make:payment-driver {name : The name of the payment gateway, e.g. Pasargad or Sadad}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new Shaparak-compliant payment gateway driver for Reyhan Commerce';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $rawName = (string) $this->argument('name');
        $studlyName = Str::studly(str_replace(['driver', 'Driver'], '', $rawName));
        $driverClass = "{$studlyName}Driver";
        $driverPath = app_path("Services/Payment/Drivers/{$driverClass}.php");

        if (File::exists($driverPath)) {
            $this->error("✖ Payment driver class already exists at: {$driverPath}");

            return self::FAILURE;
        }

        File::ensureDirectoryExists(app_path('Services/Payment/Drivers'));

        $driverContent = <<<PHP
<?php

declare(strict_types=1);

namespace Reyhan\Core\Services\Payment\Drivers;

use Reyhan\Core\Models\Order;
use Reyhan\Core\Models\Payment;
use Reyhan\Core\Services\Payment\Contracts\PaymentDriverInterface;
use Reyhan\Core\Services\Payment\DTOs\PaymentRequestResult;
use Reyhan\Core\Services\Payment\DTOs\PaymentVerifyResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

final class {$driverClass} implements PaymentDriverInterface
{
    protected string \$merchantId;

    public function __construct()
    {
        \$this->merchantId = (string) config('services.{$rawName}.merchant_id', '');
    }

    /**
     * Request payment token from gateway and generate Shaparak redirect URL.
     */
    public function request(Order \$order, string \$callbackUrl): PaymentRequestResult
    {
        try {
            \$amountRials = \$order->final_payable - \$order->wallet_paid_amount;

            // TODO: Implement HTTP request to {$studlyName} Gateway API endpoint
            /*
            \$response = Http::timeout(10)->post('https://api.gateway.ir/v1/token', [
                'merchant_id' => \$this->merchantId,
                'amount' => \$amountRials,
                'callback_url' => \$callbackUrl,
                'order_id' => \$order->order_number,
            ]);

            if (\$response->successful() && isset(\$response['authority'])) {
                return new PaymentRequestResult(
                    success: true,
                    authority: \$response['authority'],
                    redirectUrl: 'https://gateway.ir/pay/' . \$response['authority'],
                );
            }
            */

            return new PaymentRequestResult(
                success: false,
                errorMessage: __('Payment gateway initialization failed for {$studlyName}.'),
            );
        } catch (Throwable \$e) {
            Log::error('{$studlyName} Payment Request Exception: ' . \$e->getMessage());

            return new PaymentRequestResult(
                success: false,
                errorMessage: \$e->getMessage(),
            );
        }
    }

    /**
     * Verify callback response and settle transaction with {$studlyName}.
     *
     * @param  array<string, mixed>  \$payload
     */
    public function verify(Payment \$payment, array \$payload): PaymentVerifyResult
    {
        try {
            // TODO: Verify signature, authority, and status returned from callback
            return new PaymentVerifyResult(
                success: false,
                errorMessage: __('Payment verification not yet implemented for {$studlyName}.'),
            );
        } catch (Throwable \$e) {
            Log::error('{$studlyName} Payment Verify Exception: ' . \$e->getMessage());

            return new PaymentVerifyResult(
                success: false,
                errorMessage: \$e->getMessage(),
            );
        }
    }
}
PHP;

        File::put($driverPath, $driverContent);

        $this->newLine();
        $this->line("<fg=green;options=bold>✔ Payment Driver [{$driverClass}] generated successfully!</>");
        $this->line("<fg=gray>Location:</> <fg=yellow>{$driverPath}</>");
        $this->newLine();
        $this->line('  <fg=cyan>Next Steps:</>');
        $this->line('  1. Add gateway configuration credentials to <fg=white>config/services.php</>');
        $this->line('  2. Register driver in <fg=white>App\\Services\\Payment\\PaymentManager</> or via plugin');
        $this->newLine();

        return self::SUCCESS;
    }
}
