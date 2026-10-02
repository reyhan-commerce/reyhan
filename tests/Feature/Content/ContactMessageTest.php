<?php

declare(strict_types=1);

test('customer can submit a contact message with valid payload', function () {
    $payload = [
        'name' => 'مهدی رضایی',
        'mobile' => '09121112233',
        'subject' => 'پرسش درباره همکاری تجاری',
        'message' => 'سلام، شرایط اخذ نمایندگی برند شما به چه صورت است؟',
    ];

    $response = $this->postJson('/api/v1/contact', $payload);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
        ]);

    $this->assertDatabaseHas('contact_messages', [
        'name' => 'مهدی رضایی',
        'mobile' => '09121112233',
        'is_read' => false,
    ]);
});

test('contact message submission fails when required fields are missing or mobile is invalid', function () {
    $response = $this->postJson('/api/v1/contact', [
        'name' => '',
        'mobile' => 'invalid-mobile',
        'message' => '',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'mobile', 'message']);
});
