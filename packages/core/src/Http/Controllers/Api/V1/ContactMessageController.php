<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Controllers\Api\V1;

use Reyhan\Core\Http\Controllers\Controller;
use Reyhan\Core\Http\Requests\Api\V1\Content\StoreContactMessageRequest;
use Reyhan\Core\Models\ContactMessage;
use Illuminate\Http\JsonResponse;

final class ContactMessageController extends Controller
{
    /**
     * Store a customer contact message.
     */
    public function store(StoreContactMessageRequest $request): JsonResponse
    {
        $message = ContactMessage::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => __('Your message has been registered successfully. Our team will contact you shortly.'),
            'data' => [
                'id' => $message->id,
            ],
        ], 201);
    }
}
