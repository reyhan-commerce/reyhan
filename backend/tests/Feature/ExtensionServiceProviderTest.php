<?php

declare(strict_types=1);

use Reyhan\Core\Models\Order;
use Reyhan\Core\Models\Payment;
use Reyhan\Core\Models\Product;
use Reyhan\Core\Pipelines\Cart\CartCalculationPipeline;
use Reyhan\Core\Pipelines\Checkout\OrderCreationPipeline;
use Reyhan\Core\Services\Payment\Contracts\PaymentDriverInterface;
use Reyhan\Core\Services\Payment\DTOs\PaymentRequestResult;
use Reyhan\Core\Services\Payment\DTOs\PaymentVerifyResult;
use Reyhan\Core\Services\Payment\PaymentManager;
use Reyhan\Core\Support\Extensions\ReyhanExtensionServiceProvider;
use Reyhan\Core\Support\Reyhan;

class TestCustomProduct extends Product {}

class TestPaymentDriver implements PaymentDriverInterface
{
    public function request(Order $order, string $callbackUrl): PaymentRequestResult
    {
        return new PaymentRequestResult(
            authority: 'test-auth-123',
            redirectUrl: 'https://bank.test/pay',
            isRedirect: true
        );
    }

    public function verify(Payment $payment, array $payload): PaymentVerifyResult
    {
        return new PaymentVerifyResult(
            referenceId: 'ref-999',
            isSuccess: true,
            message: 'Success'
        );
    }
}

class TestDummyExtensionServiceProvider extends ReyhanExtensionServiceProvider
{
    public function register(): void
    {
        $this->registerPaymentDriver('test_driver', TestPaymentDriver::class);
        $this->swapModel('product', TestCustomProduct::class);
    }

    public function boot(): void
    {
        $this->registerCheckoutPipe('TestCheckoutPipeClass');
        $this->registerPricingPipe('TestPricingPipeClass');
    }
}

afterEach(function () {
    OrderCreationPipeline::resetPipes();
    CartCalculationPipeline::resetPipes();
    Reyhan::useModel('product', Product::class);
});

test('ReyhanExtensionServiceProvider registers drivers, pipelines and model swaps cleanly', function () {
    $provider = new TestDummyExtensionServiceProvider(app());
    $provider->register();
    $provider->boot();

    // 1. Model Swap verification
    expect(Reyhan::model('product'))->toBe(TestCustomProduct::class);

    // 2. Checkout pipeline hook verification
    $checkoutPipes = OrderCreationPipeline::getPipes();
    expect(end($checkoutPipes))->toBe('TestCheckoutPipeClass');

    // 3. Pricing pipeline hook verification
    $pricingPipes = CartCalculationPipeline::getPipes();
    expect(end($pricingPipes))->toBe('TestPricingPipeClass');

    // 4. Payment driver resolution verification
    $paymentManager = app(PaymentManager::class);
    $driver = $paymentManager->driver('test_driver');
    expect($driver)->toBeInstanceOf(TestPaymentDriver::class);
});
