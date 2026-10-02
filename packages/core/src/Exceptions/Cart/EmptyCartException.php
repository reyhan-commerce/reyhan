<?php

declare(strict_types=1);

namespace Reyhan\Core\Exceptions\Cart;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmptyCartException extends Exception
{
    public function __construct(string $message = '')
    {
        parent::__construct($message ?: __('Your shopping cart is empty.'), 422);
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
        ], 422);
    }
}
