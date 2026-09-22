<?php

declare(strict_types=1);

use App\Models\BlogCategory;
use App\Models\BlogPost;

test('blog categories endpoint returns active categories with count', function () {
    $response = $this->getJson('/api/v1/blog/categories');

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['id', 'name', 'slug', 'description', 'order', 'posts_count'],
            ],
        ]);
});

test('blog posts index returns paginated published posts', function () {
    $response = $this->getJson('/api/v1/blog/posts');

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['id', 'title', 'slug', 'summary', 'reading_time', 'views_count', 'category'],
            ],
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
        ]);
});

test('blog post show endpoint returns post details and increments views', function () {
    $category = BlogCategory::firstOrCreate(
        ['slug' => 'skin-care-test'],
        ['name' => 'پوست تستی', 'order' => 1, 'is_active' => true]
    );

    $post = BlogPost::updateOrCreate(
        ['slug' => 'test-blog-post-slug'],
        [
            'category_id' => $category->id,
            'title' => 'مقاله تست وبلاگ',
            'summary' => 'خلاصه تست',
            'content' => 'متن کامل مقاله تست برای بررسی وبلاگ.',
            'reading_time' => 3,
            'views_count' => 10,
            'is_published' => true,
            'published_at' => now(),
        ]
    );

    $response = $this->getJson('/api/v1/blog/posts/test-blog-post-slug');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.title', 'مقاله تست وبلاگ')
        ->assertJsonStructure([
            'data' => ['id', 'title', 'slug', 'content', 'summary', 'reading_time', 'views_count'],
            'related',
        ]);

    expect($post->fresh()->views_count)->toBe(11);
});

test('featured blog posts endpoint returns featured items', function () {
    $response = $this->getJson('/api/v1/blog/featured');

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'data',
        ]);
});
