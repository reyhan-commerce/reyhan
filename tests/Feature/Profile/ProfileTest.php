<?php

declare(strict_types=1);

use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('authenticated user can view profile with statistics', function () {
    $user = User::factory()->create([
        'first_name' => 'سارا',
        'last_name' => 'رضایی',
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/profile');

    $response->assertOk()
        ->assertJson([
            'success' => true,
        ])
        ->assertJsonStructure([
            'data' => [
                'user' => ['id', 'first_name', 'last_name', 'full_name', 'mobile'],
                'counts' => ['orders', 'wishlist', 'addresses', 'reviews'],
            ],
        ]);
});

test('authenticated user can update profile details', function () {
    $user = User::factory()->create([
        'first_name' => 'علی',
        'last_name' => 'محمدی',
    ]);

    Sanctum::actingAs($user);

    $newNationalCode = (string) rand(1000000000, 9999999999);
    $newEmail = 'user_' . uniqid() . '@example.com';

    $response = $this->putJson('/api/v1/profile', [
        'first_name' => 'امیرعلی',
        'last_name' => 'محمدی اصل',
        'national_code' => $newNationalCode,
        'email' => $newEmail,
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
        ]);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'first_name' => 'امیرعلی',
        'last_name' => 'محمدی اصل',
        'national_code' => $newNationalCode,
        'email' => $newEmail,
    ]);
});
