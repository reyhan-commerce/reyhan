<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Gate;
use Reyhan\Core\Models\Admin;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(DatabaseTransactions::class);

test('unauthenticated users are redirected from health check results page to admin login', function () {
    $response = $this->get('/admin/health-check-results');

    $response->assertRedirect('/admin/login');
});

test('super admin can access health check results page', function () {
    $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'admin']);
    $admin = Admin::factory()->create();
    $admin->assignRole($role);

    $response = $this->actingAs($admin, 'admin')->get('/admin/health-check-results');

    $response->assertOk();
});

test('admin with view-health permission can access health check results page', function () {
    $permission = Permission::firstOrCreate(['name' => 'view-health', 'guard_name' => 'admin']);
    $admin = Admin::factory()->create();
    $admin->givePermissionTo($permission);

    $response = $this->actingAs($admin, 'admin')->get('/admin/health-check-results');

    $response->assertOk();
});

test('admin without view-health permission cannot access health check results page', function () {
    $admin = Admin::factory()->create();

    $response = $this->actingAs($admin, 'admin')->get('/admin/health-check-results');

    $response->assertForbidden();
});

test('view-health gate ability allows super admin unconditionally', function () {
    $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'admin']);
    $admin = Admin::factory()->create();
    $admin->assignRole($role);

    expect(Gate::forUser($admin)->allows('view-health'))->toBeTrue();
});

test('view-health gate ability respects explicit permission for non-super admins', function () {
    $permission = Permission::firstOrCreate(['name' => 'view-health', 'guard_name' => 'admin']);
    $admin = Admin::factory()->create();

    expect(Gate::forUser($admin)->allows('view-health'))->toBeFalse();

    $admin->givePermissionTo($permission);

    expect(Gate::forUser($admin)->allows('view-health'))->toBeTrue();
});
