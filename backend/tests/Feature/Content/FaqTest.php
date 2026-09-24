<?php

declare(strict_types=1);

use App\Models\Faq;

test('faqs endpoint returns active faqs ordered by order column', function () {
    Faq::create([
        'question' => 'سوال تستی غیرفعال',
        'answer' => 'پاسخ تستی',
        'is_active' => false,
        'order' => 1,
    ]);

    Faq::create([
        'question' => 'سوال اول فعال',
        'answer' => 'پاسخ اول',
        'category' => 'سفارش',
        'is_active' => true,
        'order' => 10,
    ]);

    Faq::create([
        'question' => 'سوال دوم با اولویت بالاتر',
        'answer' => 'پاسخ دوم',
        'category' => 'ارسال',
        'is_active' => true,
        'order' => 2,
    ]);

    $response = $this->getJson('/api/v1/faqs');

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'data' => [
                'items' => [
                    '*' => ['id', 'question', 'answer', 'category', 'order'],
                ],
                'categories',
            ],
        ]);

    $items = $response->json('data.items');
    expect(count($items))->toBeGreaterThanOrEqual(2);
    // Inactive question should not be present
    $questions = collect($items)->pluck('question');
    expect($questions)->not->toContain('سوال تستی غیرفعال')
        ->and($questions)->toContain('سوال اول فعال');
});

test('faqs endpoint filters by category parameter', function () {
    Faq::create([
        'question' => 'سوال اختصاصی حساب',
        'answer' => 'پاسخ حساب',
        'category' => 'حساب کاربری',
        'is_active' => true,
        'order' => 1,
    ]);

    $response = $this->getJson('/api/v1/faqs?category='.urlencode('حساب کاربری'));

    $response->assertOk();
    $items = $response->json('data.items');
    foreach ($items as $item) {
        expect($item['category'])->toBe('حساب کاربری');
    }
});
