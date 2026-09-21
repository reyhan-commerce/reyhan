<?php

declare(strict_types=1);

use App\Models\Category;
use App\Services\Catalog\CategoryService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    Cache::flush();
});

it('returns category tree cached in redis', function (): void {
    $parent = Category::create([
        'name' => 'مراقبت پوست',
        'slug' => 'skincare-tree',
        'is_active' => true,
        'order' => 1,
    ]);

    Category::create([
        'parent_id' => $parent->id,
        'name' => 'ضدآفتاب',
        'slug' => 'sunscreen-tree',
        'is_active' => true,
        'order' => 1,
    ]);

    expect(Cache::has(CategoryService::CACHE_KEY_TREE))->toBeFalse();

    $response = $this->getJson(route('categories.tree'));

    $response->assertOk()
        ->assertJson([
            'success' => true,
        ])
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['id', 'name', 'slug', 'children'],
            ],
        ]);

    expect(Cache::has(CategoryService::CACHE_KEY_TREE))->toBeTrue();
});

it('returns category tree with correct hierarchy', function (): void {
    $parent = Category::create([
        'name' => 'آرایشی',
        'slug' => 'makeup-root',
        'is_active' => true,
        'order' => 1,
    ]);

    $child = Category::create([
        'parent_id' => $parent->id,
        'name' => 'رژلب',
        'slug' => 'lipstick-child',
        'is_active' => true,
        'order' => 1,
    ]);

    $response = $this->getJson(route('categories.tree'));

    $response->assertOk();
    $data = $response->json('data');

    $makeupCategory = collect($data)->firstWhere('slug', 'makeup-root');
    expect($makeupCategory)->not->toBeNull()
        ->and($makeupCategory['children'])->toHaveCount(1)
        ->and($makeupCategory['children'][0]['slug'])->toBe('lipstick-child');
});

it('returns paginated active categories', function (): void {
    Category::create([
        'name' => 'عطر و ادکلن',
        'slug' => 'fragrance-cat',
        'is_active' => true,
    ]);

    $response = $this->getJson(route('categories.index'));

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'slug'],
            ],
            'meta',
            'links',
            'success',
        ]);
});

it('shows single category with children and attributes', function (): void {
    $cat = Category::create([
        'name' => 'مراقبت مو',
        'slug' => 'haircare-detail',
        'is_active' => true,
    ]);

    $response = $this->getJson(route('categories.show', ['category' => 'haircare-detail']));

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'name' => 'مراقبت مو',
                'slug' => 'haircare-detail',
            ],
        ]);
});

it('returns 404 for non-existent category slug', function (): void {
    $response = $this->getJson(route('categories.show', ['category' => 'non-existent-cat-slug']));

    $response->assertNotFound();
});
