<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    /**
     * Store a customer contact message.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'max:20', 'regex:/^09[0-9]{9}$/'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:3000'],
        ], [
            'name.required' => 'وارد کردن نام و نام خانوادگی الزامی است.',
            'mobile.required' => 'وارد کردن شماره همراه الزامی است.',
            'mobile.regex' => 'فرمت شماره موبایل نامعتبر است (مثال: ۰۹۱۲۳۴۵۶۷۸۹).',
            'message.required' => 'وارد کردن متن پیام الزامی است.',
            'message.max' => 'طول متن پیام نمی‌تواند بیش از ۳۰۰۰ کاراکتر باشد.',
        ]);

        $message = ContactMessage::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'پیام شما با موفقیت ثبت شد. کارشناسان ما به زودی با شما تماس خواهند گرفت.',
            'data' => [
                'id' => $message->id,
            ],
        ], 201);
    }
}
