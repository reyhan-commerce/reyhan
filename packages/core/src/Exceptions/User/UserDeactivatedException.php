<?php

declare(strict_types=1);

namespace Reyhan\Core\Exceptions\User;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserDeactivatedException extends Exception
{
    public function __construct(string $message = '')
    {
        $msg = $message ?: __('Your account has been deactivated.');
        parent::__construct($msg, 403);
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
