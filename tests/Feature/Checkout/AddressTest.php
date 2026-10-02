<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Reyhan\Core\Models\Address;
use Reyhan\Core\Models\City;
use Reyhan\Core\Models\Province;
use Reyhan\Core\Models\User;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    $this->user = User::factory()->create([
        'mobile' => '09123456789',
        'is_active' => true,
    ]);

    $this->province = Province::factory()->create([
        'name' => 'تهران',
        'slug' => 'tehran-test-'.uniqid(),
        'order' => 1,
    ]);

    $this->city = City::factory()->create([
        'province_id' => $this->province->id,
        'name' => 'تهران',
        'slug' => 'tehran-city-test-'.uniqid(),
        'postal_prefix' => '11',
        'order' => 1,
    ]);
});

it('lists user addresses with default first', function (): void {
    Sanctum::actingAs($this->user);

    Address::factory()->create([
        'user_id' => $this->user->id,
        'province_id' => $this->province->id,
        'city_id' => $this->city->id,
        'recipient_name' => 'علی تستی',
        'recipient_mobile' => '09123456789',
        'postal_code' => '1234567890',
        'address_line' => 'خیابان تست ۱',
        'is_default' => false,
    ]);

    $defaultAddress = Address::factory()->default()->create([
        'user_id' => $this->user->id,
        'province_id' => $this->province->id,
        'city_id' => $this->city->id,
        'recipient_name' => 'علی پیش‌فرض',
        'recipient_mobile' => '09123456789',
        'postal_code' => '1234567891',
        'address_line' => 'خیابان تست ۲',
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

    $addr1 = Address::factory()->default()->create([
        'user_id' => $this->user->id,
        'province_id' => $this->province->id,
        'city_id' => $this->city->id,
        'recipient_name' => 'آدرس یک',
        'recipient_mobile' => '09123456789',
        'postal_code' => '1111111111',
        'address_line' => 'آدرس ۱',
    ]);

    $addr2 = Address::factory()->create([
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

it('forbids updating or deleting an address belonging to another user', function (): void {
    $otherUser = User::factory()->create([
        'mobile' => '09987654321',
        'is_active' => true,
    ]);

    $foreignAddress = Address::factory()->default()->create([
        'user_id' => $otherUser->id,
        'province_id' => $this->province->id,
        'city_id' => $this->city->id,
        'recipient_name' => 'کاربر دیگر',
        'recipient_mobile' => '09987654321',
        'postal_code' => '9999999999',
        'address_line' => 'نشانی کاربر دیگر',
    ]);

    Sanctum::actingAs($this->user);

    $this->putJson(route('addresses.update', $foreignAddress->id), [
        'recipient_name' => 'تغییر غیرمجاز',
    ])->assertForbidden();

    $this->deleteJson(route('addresses.destroy', $foreignAddress->id))
        ->assertForbidden();

    $this->patchJson(route('addresses.default', $foreignAddress->id))
        ->assertForbidden();
});
