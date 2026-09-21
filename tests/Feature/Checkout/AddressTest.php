<?php

declare(strict_types=1);

use App\Models\Address;
use App\Models\City;
use App\Models\Province;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    $this->user = User::factory()->create([
        'mobile' => '09123456789',
        'is_active' => true,
    ]);

    $this->province = Province::create([
        'name' => 'تهران',
        'slug' => 'tehran-test-' . uniqid(),
        'order' => 1,
    ]);

    $this->city = City::create([
        'province_id' => $this->province->id,
        'name' => 'تهران',
        'slug' => 'tehran-city-test-' . uniqid(),
        'postal_prefix' => '11',
        'order' => 1,
    ]);
});

it('lists user addresses with default first', function (): void {
    Sanctum::actingAs($this->user);

    Address::create([
        'user_id' => $this->user->id,
        'province_id' => $this->province->id,
        'city_id' => $this->city->id,
        'recipient_name' => 'علی تستی',
        'recipient_mobile' => '09123456789',
        'postal_code' => '1234567890',
        'address_line' => 'خیابان تست ۱',
        'is_default' => false,
    ]);

    $defaultAddress = Address::create([
        'user_id' => $this->user->id,
        'province_id' => $this->province->id,
        'city_id' => $this->city->id,
        'recipient_name' => 'علی پیش‌فرض',
        'recipient_mobile' => '09123456789',
        'postal_code' => '1234567891',
        'address_line' => 'خیابان تست ۲',
        'is_default' => true,
    ]);

    $response = $this->getJson(route('addresses.index'));

    $response->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.id', $defaultAddress->id)
        ->assertJsonPath('data.0.is_default', true);
});

it('creates address and sets default automatically if first address', function (): void {
    Sanctum::actingAs($this->user);

    $payload = [
        'province_id' => $this->province->id,
        'city_id' => $this->city->id,
        'recipient_name' => 'سارا رضایی',
        'recipient_mobile' => '09123456780',
        'postal_code' => '1485963214',
        'address_line' => 'میدان ونک، خیابان ولیعصر، پلاک ۱۰',
        'building_number' => '۱۰',
        'unit' => '۴',
    ];

    $response = $this->postJson(route('addresses.store'), $payload);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.is_default', true)
        ->assertJsonPath('data.recipient_name', 'سارا رضایی');

    $this->assertDatabaseHas('addresses', [
        'user_id' => $this->user->id,
        'postal_code' => '1485963214',
        'is_default' => true,
    ]);
});

it('sets address as default and unsets others', function (): void {
    Sanctum::actingAs($this->user);

    $addr1 = Address::create([
        'user_id' => $this->user->id,
        'province_id' => $this->province->id,
        'city_id' => $this->city->id,
        'recipient_name' => 'آدرس یک',
        'recipient_mobile' => '09123456789',
        'postal_code' => '1111111111',
        'address_line' => 'آدرس ۱',
        'is_default' => true,
    ]);

    $addr2 = Address::create([
        'user_id' => $this->user->id,
        'province_id' => $this->province->id,
        'city_id' => $this->city->id,
        'recipient_name' => 'آدرس دو',
        'recipient_mobile' => '09123456789',
        'postal_code' => '2222222222',
        'address_line' => 'آدرس ۲',
        'is_default' => false,
    ]);

    $response = $this->patchJson(route('addresses.default', $addr2->id));

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.is_default', true);

    expect($addr1->fresh()->is_default)->toBeFalse();
    expect($addr2->fresh()->is_default)->toBeTrue();
});
