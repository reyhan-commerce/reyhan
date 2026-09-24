<?php

declare(strict_types=1);

use App\Models\Admin;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(DatabaseTransactions::class);

test('unauthenticated users are redirected from backups page to admin login', function () {
    $response = $this->get('/admin/backups');

    $response->assertRedirect('/admin/login');
});

test('super admin can access backups page', function () {
    $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'admin']);
    $admin = Admin::factory()->create();
    $admin->assignRole($role);

    $response = $this->actingAs($admin, 'admin')->get('/admin/backups');

    $response->assertOk();
});

test('admin with view-backups permission can access backups page', function () {
    $permission = Permission::firstOrCreate(['name' => 'view-backups', 'guard_name' => 'admin']);
    $admin = Admin::factory()->create();
    $admin->givePermissionTo($permission);

    $response = $this->actingAs($admin, 'admin')->get('/admin/backups');

    $response->assertOk();
});

test('admin without backup permission cannot access backups page', function () {
    $admin = Admin::factory()->create();

    $response = $this->actingAs($admin, 'admin')->get('/admin/backups');

    $response->assertForbidden();
});

test('backup gate abilities allow super admin unconditionally', function () {
    $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'admin']);
    $admin = Admin::factory()->create();
    $admin->assignRole($role);

    expect(Gate::forUser($admin)->allows('view-backups'))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('create-backup'))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('download-backup'))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('delete-backup'))->toBeTrue();
});

test('backup gate abilities respect explicit permissions for non-super admins', function () {
    $createPerm = Permission::firstOrCreate(['name' => 'create-backup', 'guard_name' => 'admin']);
    $downloadPerm = Permission::firstOrCreate(['name' => 'download-backup', 'guard_name' => 'admin']);

    $admin = Admin::factory()->create();

    expect(Gate::forUser($admin)->allows('create-backup'))->toBeFalse()
        ->and(Gate::forUser($admin)->allows('download-backup'))->toBeFalse()
        ->and(Gate::forUser($admin)->allows('delete-backup'))->toBeFalse();

    $admin->givePermissionTo([$createPerm, $downloadPerm]);

    expect(Gate::forUser($admin)->allows('create-backup'))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('download-backup'))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('delete-backup'))->toBeFalse();
});
