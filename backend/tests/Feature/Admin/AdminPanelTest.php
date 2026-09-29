<?php

declare(strict_types=1);

use App\Enums\PaymentGateway;
use App\Models\Admin;
use App\Models\Payment;
use App\Models\User;
use Filament\Auth\Pages\Login;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

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
    $admin = Admin::factory()->create();

    $response = $this->actingAs($admin, 'admin')->get('/admin');

    $response->assertOk();
});

test('deactivated admin cannot access admin panel', function () {
    $admin = Admin::factory()->inactive()->create();

    $response = $this->actingAs($admin, 'admin')->get('/admin');

    $response->assertForbidden();
});

test('admin can access general settings page', function () {
    $admin = Admin::factory()->create();

    $response = $this->actingAs($admin, 'admin')->get('/admin/manage-general-settings');

    $response->assertOk();
});

test('admin can access sms settings page', function () {
    $admin = Admin::factory()->create();

    $response = $this->actingAs($admin, 'admin')->get('/admin/manage-sms-settings');

    $response->assertOk();
});

test('admin can access payments list with diverse gateway records', function () {
    $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'admin']);
    $admin = Admin::factory()->create();
    $admin->assignRole($role);

    Payment::factory()->create([
        'gateway' => PaymentGateway::SnappPay,
    ]);
    Payment::factory()->create([
        'gateway' => PaymentGateway::Wallet,
    ]);
    Payment::factory()->create([
        'gateway' => PaymentGateway::CardToCard,
    ]);

    $response = $this->actingAs($admin, 'admin')->get('/admin/payments');

    $response->assertOk();
});
test('admin can log in through the login form and authenticate session', function () {
    $admin = Admin::factory()->create([
        'email' => 'login_test@easyshop.local',
        'password' => 'password123',
        'is_active' => true,
    ]);

    Livewire::test(Login::class)
        ->set('data.email', 'login_test@easyshop.local')
        ->set('data.password', 'password123')
        ->call('authenticate')
        ->assertHasNoErrors()
        ->assertRedirect('/admin');

    $this->assertAuthenticatedAs($admin, 'admin');
});
