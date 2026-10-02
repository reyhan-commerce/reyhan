<?php

declare(strict_types=1);

use Reyhan\Core\Filament\Resources\Categories\Pages\CreateCategory;
use Reyhan\Core\Filament\Resources\Categories\Pages\EditCategory;
use Reyhan\Core\Filament\Resources\Categories\Pages\ListCategories;
use Reyhan\Core\Models\Admin;
use Reyhan\Core\Models\Category;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'admin']);
    $this->admin = Admin::factory()->create();
    $this->admin->assignRole($role);
});

test('admin can access categories list page', function (): void {
    $response = $this->actingAs($this->admin, 'admin')->get('/admin/categories');

    $response->assertOk();
});

test('category list renders tree table with records', function (): void {
    $parent = Category::factory()->create([
        'name' => 'پوست و مو',
        'order' => 1,
    ]);

    $child = Category::factory()->childOf($parent)->create([
        'name' => 'شامپو ضد شوره',
        'order' => 1,
    ]);

    Livewire::actingAs($this->admin, 'admin')
        ->test(ListCategories::class)
        ->assertSuccessful()
        ->assertSee('پوست و مو')
        ->assertSee('شامپو ضد شوره');
});

test('admin can access create category page', function (): void {
    $response = $this->actingAs($this->admin, 'admin')->get('/admin/categories/create');

    $response->assertOk();

    Livewire::actingAs($this->admin, 'admin')
        ->test(CreateCategory::class)
        ->assertSuccessful();
});

test('admin can access edit category page', function (): void {
    $category = Category::factory()->create([
        'name' => 'آرایشی و زیبایی',
    ]);

    $response = $this->actingAs($this->admin, 'admin')->get("/admin/categories/{$category->slug}/edit");

    $response->assertOk();

    Livewire::actingAs($this->admin, 'admin')
        ->test(EditCategory::class, ['record' => $category->getRouteKey()])
        ->assertSuccessful()
        ->assertSchemaStateSet([
            'name' => 'آرایشی و زیبایی',
        ]);
});
