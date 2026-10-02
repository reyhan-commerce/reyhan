<?php

declare(strict_types=1);

namespace Reyhan\Core\Exceptions\Feature;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeatureDisabledException extends Exception
{
    public function __construct(string $message = '')
    {
        parent::__construct($message ?: __('Customer loyalty club is currently inactive.'), 403);
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
        ], 403);
    }
}
