<?php

declare(strict_types=1);

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

test('unauthenticated users are redirected from admin dashboard to admin login', function () {
    $response = $this->get('/admin');

    $response->assertRedirect('/admin/login');
});

test('regular users cannot access admin panel', function () {
    $user = User::factory()->create([
        'mobile' => '09129998877',
        'is_active' => true,
    ]);

    $response = $this->actingAs($user, 'sanctum')->get('/admin');

    $response->assertRedirect('/admin/login');
});

test('active admin can access admin panel', function () {
    $admin = Admin::create([
        'name' => 'ادمین تست',
        'email' => 'test-admin@easyshop.local',
        'password' => bcrypt('password'),
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin, 'admin')->get('/admin');

    $response->assertOk();
});

test('deactivated admin cannot access admin panel', function () {
    $admin = Admin::create([
        'name' => 'ادمین مسدود',
        'email' => 'blocked-admin@easyshop.local',
        'password' => bcrypt('password'),
        'is_active' => false,
    ]);

    $response = $this->actingAs($admin, 'admin')->get('/admin');

    $response->assertForbidden();
});
