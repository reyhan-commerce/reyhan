<?php

declare(strict_types=1);

namespace Tests\Feature\System;

use Reyhan\Core\Models\Order;
use Reyhan\Core\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\File;

uses(DatabaseTransactions::class);

afterEach(function () {
    if (File::exists(base_path('extensions/sample-plugin'))) {
        File::deleteDirectory(base_path('extensions/sample-plugin'));
    }

    if (File::exists(app_path('Services/Payment/Drivers/PasargadDriver.php'))) {
        File::delete(app_path('Services/Payment/Drivers/PasargadDriver.php'));
    }

    if (File::exists(app_path('Services/Shipping/Drivers/ChaparShippingDriver.php'))) {
        File::delete(app_path('Services/Shipping/Drivers/ChaparShippingDriver.php'));
    }
});

test('reyhan:make:plugin scaffolds extension structure correctly', function () {
    $this->artisan('reyhan:make:plugin', ['name' => 'SamplePlugin'])
        ->assertSuccessful();

    expect(File::exists(base_path('extensions/sample-plugin/composer.json')))->toBeTrue()
        ->and(File::exists(base_path('extensions/sample-plugin/src/SamplePluginServiceProvider.php')))->toBeTrue()
        ->and(File::exists(base_path('extensions/sample-plugin/config/sample_plugin.php')))->toBeTrue()
        ->and(File::exists(base_path('extensions/sample-plugin/routes/api.php')))->toBeTrue();
});

test('reyhan:make:payment-driver creates payment driver boilerplate', function () {
    $this->artisan('reyhan:make:payment-driver', ['name' => 'Pasargad'])
        ->assertSuccessful();

    $path = app_path('Services/Payment/Drivers/PasargadDriver.php');
    expect(File::exists($path))->toBeTrue()
        ->and(File::get($path))->toContain('final class PasargadDriver implements PaymentDriverInterface');
});

test('reyhan:make:shipping-driver creates shipping driver boilerplate', function () {
    $this->artisan('reyhan:make:shipping-driver', ['name' => 'Chapar'])
        ->assertSuccessful();

    $path = app_path('Services/Shipping/Drivers/ChaparShippingDriver.php');
    expect(File::exists($path))->toBeTrue()
        ->and(File::get($path))->toContain('final class ChaparShippingDriver implements ShippingDriverInterface');
});

test('reyhan:tax:export-moadian exports settled orders', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'paid_at' => now(),
        'items_subtotal' => 1000000,
        'tax_amount' => 100000,
        'final_payable' => 1100000,
    ]);

    $outputPath = storage_path('app/tax/test_moadian_export.json');

    $this->artisan('reyhan:tax:export-moadian', [
        '--format' => 'json',
        '--output' => $outputPath,
    ])->assertSuccessful();

    expect(File::exists($outputPath))->toBeTrue();

    $json = json_decode(File::get($outputPath), true);
    expect($json)->toBeArray()
        ->and($json['invoice_count'])->toBeGreaterThanOrEqual(1);

    File::delete($outputPath);
});
