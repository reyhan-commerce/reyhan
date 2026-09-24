<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Models\Admin;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

uses(DatabaseTransactions::class);

test('order creation and status update are recorded in activity log', function () {
    $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'admin']);
    $admin = Admin::factory()->create();
    $admin->assignRole($role);

    $user = User::factory()->create();

    $this->actingAs($admin, 'admin');

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'order_number' => 'ORD-TEST-999',
        'status' => OrderStatus::PendingPayment,
    ]);

    $creationLog = Activity::forSubject($order)->first();
    expect($creationLog)->not->toBeNull()
        ->and($order->activityTitle())->toBe('ORD-TEST-999');

    // Update status
    $order->update([
        'status' => OrderStatus::Processing,
    ]);

    $updateLog = Activity::forSubject($order)->latest('id')->first();
    expect($updateLog)->not->toBeNull()
        ->and($updateLog->causer_id)->toBe($admin->id);
});

test('admin can access activities audit log page in panel', function () {
    $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'admin']);
    $admin = Admin::factory()->create();
    $admin->assignRole($role);

    $response = $this->actingAs($admin, 'admin')->get('/admin/activities');

    $response->assertOk();
});

test('admin can view order page with timeline action', function () {
    $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'admin']);
    $admin = Admin::factory()->create();
    $admin->assignRole($role);

    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'order_number' => 'ORD-TEST-1000',
        'status' => OrderStatus::PendingPayment,
    ]);

    $response = $this->actingAs($admin, 'admin')->get("/admin/orders/{$order->id}");

    $response->assertOk();
});
